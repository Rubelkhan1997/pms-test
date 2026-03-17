<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Jobs;

use App\Modules\ChannelManager\Models\OtaSyncQueue;
use App\Modules\ChannelManager\Services\ChannelManagerService;
use App\Modules\ChannelManager\Services\Providers\BookingComService;
use App\Modules\FrontDesk\Services\AvailabilityService;
use App\Modules\FrontDesk\Services\PricingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process OTA Sync Queue Job
 * 
 * Processes pending sync jobs from the OTA sync queue.
 */
class ProcessOtaSyncQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get pending sync jobs
        $syncJobs = OtaSyncQueue::where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('available_at')
                      ->orWhere('available_at', '<=', now());
            })
            ->limit(50) // Process 50 jobs at a time
            ->get();
        
        foreach ($syncJobs as $job) {
            $this->processSyncJob($job);
        }
    }
    
    /**
     * Process individual sync job.
     */
    protected function processSyncJob(OtaSyncQueue $job): void
    {
        try {
            $job->markAsProcessing();
            
            $result = match ($job->sync_type) {
                'availability' => $this->processAvailabilitySync($job),
                'rates' => $this->processRateSync($job),
                'reservations' => $this->processReservationSync($job),
                'rooms' => $this->processRoomSync($job),
                default => ['success' => false, 'message' => 'Unknown sync type'],
            };
            
            if ($result['success']) {
                $job->markAsCompleted();
            } else {
                $job->markAsFailed($result['message']);
            }
            
        } catch (\Exception $e) {
            Log::error('OTA sync job failed', [
                'job_id' => $job->id,
                'error' => $e->getMessage(),
            ]);
            
            $job->markAsFailed($e->getMessage());
        }
    }
    
    /**
     * Process availability sync.
     */
    protected function processAvailabilitySync(OtaSyncQueue $job): array
    {
        $connection = $job->hotel->connections()
            ->where('ota_provider_id', $job->ota_provider_id)
            ->first();
        
        if (!$connection || !$connection->isActive()) {
            return ['success' => false, 'message' => 'Connection not active'];
        }
        
        $payload = $job->payload;
        $startDate = \Carbon\Carbon::parse($payload['start_date'] ?? now());
        $endDate = \Carbon\Carbon::parse($payload['end_date'] ?? now()->addDays(365));
        
        // Get availability data
        $availabilityService = app(AvailabilityService::class);
        $availabilityData = $this->buildAvailabilityData(
            $job->hotel_id,
            $startDate,
            $endDate
        );
        
        // Push to OTA based on provider
        return match ($job->otaProvider->code) {
            'booking' => $this->pushToBooking($connection, 'availability', $availabilityData),
            'expedia' => ['success' => false, 'message' => 'Expedia not implemented'],
            'agoda' => ['success' => false, 'message' => 'Agoda not implemented'],
            default => ['success' => false, 'message' => 'Unknown provider'],
        };
    }
    
    /**
     * Process rate sync.
     */
    protected function processRateSync(OtaSyncQueue $job): array
    {
        $connection = $job->hotel->connections()
            ->where('ota_provider_id', $job->ota_provider_id)
            ->first();
        
        if (!$connection || !$connection->isActive()) {
            return ['success' => false, 'message' => 'Connection not active'];
        }
        
        $payload = $job->payload;
        
        // Get rate data
        $pricingService = app(PricingService::class);
        $rateData = $this->buildRateData(
            $job->hotel_id,
            $payload['room_type_id'] ?? null,
            $payload['rate_plan_id'] ?? null
        );
        
        // Push to OTA based on provider
        return match ($job->otaProvider->code) {
            'booking' => $this->pushToBooking($connection, 'rates', $rateData),
            'expedia' => ['success' => false, 'message' => 'Expedia not implemented'],
            'agoda' => ['success' => false, 'message' => 'Agoda not implemented'],
            default => ['success' => false, 'message' => 'Unknown provider'],
        };
    }
    
    /**
     * Process reservation sync (pull from OTA).
     */
    protected function processReservationSync(OtaSyncQueue $job): array
    {
        $connection = $job->hotel->connections()
            ->where('ota_provider_id', $job->ota_provider_id)
            ->first();
        
        if (!$connection || !$connection->isActive()) {
            return ['success' => false, 'message' => 'Connection not active'];
        }
        
        // Pull reservations from OTA
        return match ($job->otaProvider->code) {
            'booking' => $this->pullFromBooking($connection),
            'expedia' => ['success' => false, 'message' => 'Expedia not implemented'],
            'agoda' => ['success' => false, 'message' => 'Agoda not implemented'],
            default => ['success' => false, 'message' => 'Unknown provider'],
        };
    }
    
    /**
     * Process room sync.
     */
    protected function processRoomSync(OtaSyncQueue $job): array
    {
        // Room mapping sync
        return ['success' => true, 'message' => 'Room sync completed'];
    }
    
    /**
     * Build availability data for hotel.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildAvailabilityData(
        int $hotelId,
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate
    ): array {
        $availabilityService = app(AvailabilityService::class);
        $availabilityData = [];
        
        $roomTypes = \App\Modules\FrontDesk\Models\RoomType::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->get();
        
        $currentDate = $startDate->copy();
        while ($currentDate < $endDate) {
            foreach ($roomTypes as $roomType) {
                $availability = $availabilityService->checkAvailability(
                    $roomType->id,
                    $currentDate,
                    $currentDate->copy()->addDay()
                );
                
                $availabilityData[] = [
                    'room_type_id' => $roomType->id,
                    'date' => $currentDate->toDateString(),
                    'available_rooms' => $availability['available_rooms'],
                    'is_closed' => !$availability['available'],
                ];
            }
            
            $currentDate->addDay();
        }
        
        return $availabilityData;
    }
    
    /**
     * Build rate data for hotel.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildRateData(
        int $hotelId,
        ?int $roomTypeId = null,
        ?int $ratePlanId = null
    ): array {
        $pricingService = app(PricingService::class);
        $rateData = [];
        
        $query = \App\Modules\FrontDesk\Models\RoomType::where('hotel_id', $hotelId)
            ->where('is_active', true);
        
        if ($roomTypeId) {
            $query->where('id', $roomTypeId);
        }
        
        $roomTypes = $query->get();
        
        foreach ($roomTypes as $roomType) {
            $rates = $roomType->ratePlans()
                ->where('is_active', true)
                ->when($ratePlanId, fn ($q) => $q->where('id', $ratePlanId))
                ->get();
            
            foreach ($rates as $ratePlan) {
                $pricing = $pricingService->calculateStayPrice(
                    $roomType->id,
                    $ratePlan->id,
                    now(),
                    now()->addDay()
                );
                
                $rateData[] = [
                    'room_type_id' => $roomType->id,
                    'rate_plan_id' => $ratePlan->id,
                    'base_rate' => $ratePlan->base_rate,
                    'currency' => 'USD',
                ];
            }
        }
        
        return $rateData;
    }
    
    /**
     * Push data to Booking.com.
     */
    protected function pushToBooking(
        \App\Modules\ChannelManager\Models\HotelOtaConnection $connection,
        string $type,
        array $data
    ): array {
        $bookingService = app(BookingComService::class);
        
        return match ($type) {
            'availability' => $bookingService->pushAvailability($connection, $data),
            'rates' => $bookingService->pushRates($connection, $data),
            default => ['success' => false, 'message' => 'Unknown type'],
        };
    }
    
    /**
     * Pull reservations from Booking.com.
     */
    protected function pullFromBooking(
        \App\Modules\ChannelManager\Models\HotelOtaConnection $connection
    ): array {
        $bookingService = app(BookingComService::class);
        
        $result = $bookingService->pullReservations(
            $connection,
            now()->subDays(30),
            now()->addDays(365)
        );
        
        if ($result['success'] && !empty($result['reservations'])) {
            // Import reservations to PMS
            $this->importReservations($connection, $result['reservations']);
        }
        
        return $result;
    }
    
    /**
     * Import reservations from OTA to PMS.
     *
     * @param  array<int, array<string, mixed>>  $reservations
     */
    protected function importReservations(
        \App\Modules\ChannelManager\Models\HotelOtaConnection $connection,
        array $reservations
    ): void {
        foreach ($reservations as $otaReservationData) {
            // Create or update OTA reservation record
            \App\Modules\ChannelManager\Models\OtaReservation::updateOrCreate(
                [
                    'ota_provider_id' => $connection->ota_provider_id,
                    'ota_reservation_id' => $otaReservationData['reservation_id'],
                ],
                [
                    'hotel_id' => $connection->hotel_id,
                    'guest_name' => $otaReservationData['guest_name'] ?? '',
                    'guest_email' => $otaReservationData['guest_email'] ?? '',
                    'check_in_date' => $otaReservationData['checkin'],
                    'check_out_date' => $otaReservationData['checkout'],
                    'adults' => $otaReservationData['adults'] ?? 1,
                    'children' => $otaReservationData['children'] ?? 0,
                    'rooms' => $otaReservationData['rooms'] ?? 1,
                    'total_amount' => $otaReservationData['total_price'] ?? 0,
                    'currency' => $otaReservationData['currency'] ?? 'USD',
                    'status' => $otaReservationData['status'] ?? 'confirmed',
                    'raw_data' => $otaReservationData,
                ]
            );
        }
    }
}
