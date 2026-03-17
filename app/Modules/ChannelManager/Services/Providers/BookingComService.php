<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Services\Providers;

use App\Modules\ChannelManager\Models\HotelOtaConnection;
use App\Modules\ChannelManager\Models\OtaSyncLog;
use App\Modules\ChannelManager\Services\ChannelManagerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Booking.com Provider Service
 * 
 * Implements Booking.com Connectivity API (XML/JSON).
 * 
 * Documentation: https://admin.extranet.booking.com/docs
 */
class BookingComService
{
    protected string $baseUrl = 'https://connect.booking.com';
    protected string $apiVersion = 'v1';
    
    /**
     * Get hotel credentials.
     */
    protected function getCredentials(HotelOtaConnection $connection): array
    {
        return $connection->credentials ?? [];
    }
    
    /**
     * Get authentication headers.
     */
    protected function getAuthHeaders(HotelOtaConnection $connection): array
    {
        $credentials = $this->getCredentials($connection);
        
        return [
            'Authorization' => 'Basic ' . base64_encode("{$credentials['username']}:{$credentials['password']}"),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
    
    /**
     * Push availability to Booking.com.
     *
     * @param  HotelOtaConnection  $connection  Hotel connection
     * @param  array<int, array<string, mixed>>  $availability  Availability data
     * @return array{success: bool, message: string, response: array}
     */
    public function pushAvailability(HotelOtaConnection $connection, array $availability): array
    {
        $startTime = microtime(true);
        
        try {
            $response = Http::withHeaders($this->getAuthHeaders($connection))
                ->timeout(30)
                ->post("{$this->baseUrl}/{$this->apiVersion}/availability", [
                    'property_id' => $connection->property_id,
                    'availabilities' => $availability,
                ]);
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            if ($response->successful()) {
                $this->logSync(
                    $connection,
                    'availability',
                    'push',
                    true,
                    $availability,
                    $response->json(),
                    $response->status(),
                    null,
                    $executionTime
                );
                
                return [
                    'success' => true,
                    'message' => 'Availability pushed successfully',
                    'response' => $response->json(),
                ];
            }
            
            $this->logSync(
                $connection,
                'availability',
                'push',
                false,
                $availability,
                $response->json(),
                $response->status(),
                $response->body(),
                $executionTime
            );
            
            return [
                'success' => false,
                'message' => 'Failed to push availability',
                'response' => $response->json(),
            ];
            
        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            $this->logSync(
                $connection,
                'availability',
                'push',
                false,
                $availability,
                null,
                null,
                $e->getMessage(),
                $executionTime
            );
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'response' => [],
            ];
        }
    }
    
    /**
     * Push rates to Booking.com.
     *
     * @param  HotelOtaConnection  $connection  Hotel connection
     * @param  array<int, array<string, mixed>>  $rates  Rate data
     * @return array{success: bool, message: string, response: array}
     */
    public function pushRates(HotelOtaConnection $connection, array $rates): array
    {
        $startTime = microtime(true);
        
        try {
            $response = Http::withHeaders($this->getAuthHeaders($connection))
                ->timeout(30)
                ->post("{$this->baseUrl}/{$this->apiVersion}/rates", [
                    'property_id' => $connection->property_id,
                    'rates' => $rates,
                ]);
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            if ($response->successful()) {
                $this->logSync(
                    $connection,
                    'rates',
                    'push',
                    true,
                    $rates,
                    $response->json(),
                    $response->status(),
                    null,
                    $executionTime
                );
                
                return [
                    'success' => true,
                    'message' => 'Rates pushed successfully',
                    'response' => $response->json(),
                ];
            }
            
            $this->logSync(
                $connection,
                'rates',
                'push',
                false,
                $rates,
                $response->json(),
                $response->status(),
                $response->body(),
                $executionTime
            );
            
            return [
                'success' => false,
                'message' => 'Failed to push rates',
                'response' => $response->json(),
            ];
            
        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            $this->logSync(
                $connection,
                'rates',
                'push',
                false,
                $rates,
                null,
                null,
                $e->getMessage(),
                $executionTime
            );
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'response' => [],
            ];
        }
    }
    
    /**
     * Pull reservations from Booking.com.
     *
     * @param  HotelOtaConnection  $connection  Hotel connection
     * @param  \Carbon\Carbon  $startDate  Start date
     * @param  \Carbon\Carbon  $endDate  End date
     * @return array{success: bool, reservations: array, response: array}
     */
    public function pullReservations(
        HotelOtaConnection $connection,
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate
    ): array {
        $startTime = microtime(true);
        
        try {
            $response = Http::withHeaders($this->getAuthHeaders($connection))
                ->timeout(30)
                ->get("{$this->baseUrl}/{$this->apiVersion}/reservations", [
                    'property_id' => $connection->property_id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ]);
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            if ($response->successful()) {
                $this->logSync(
                    $connection,
                    'reservations',
                    'pull',
                    true,
                    ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()],
                    $response->json(),
                    $response->status(),
                    null,
                    $executionTime
                );
                
                $data = $response->json();
                
                return [
                    'success' => true,
                    'reservations' => $data['reservations'] ?? [],
                    'response' => $data,
                ];
            }
            
            $this->logSync(
                $connection,
                'reservations',
                'pull',
                false,
                ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()],
                $response->json(),
                $response->status(),
                $response->body(),
                $executionTime
            );
            
            return [
                'success' => false,
                'reservations' => [],
                'response' => $response->json(),
            ];
            
        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            $this->logSync(
                $connection,
                'reservations',
                'pull',
                false,
                ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()],
                null,
                null,
                $e->getMessage(),
                $executionTime
            );
            
            return [
                'success' => false,
                'reservations' => [],
                'response' => [],
            ];
        }
    }
    
    /**
     * Test connection to Booking.com.
     */
    public function testConnection(HotelOtaConnection $connection): bool
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders($connection))
                ->timeout(10)
                ->get("{$this->baseUrl}/{$this->apiVersion}/hotels/{$connection->property_id}");
            
            return $response->successful();
            
        } catch (\Exception $e) {
            Log::error('Booking.com connection test failed', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Log sync operation.
     */
    protected function logSync(
        HotelOtaConnection $connection,
        string $syncType,
        string $direction,
        bool $success,
        ?array $requestData = null,
        ?array $responseData = null,
        ?int $responseCode = null,
        ?string $errorMessage = null,
        ?float $executionTime = null
    ): void {
        $channelService = app(ChannelManagerService::class);
        
        $channelService->logSync(
            hotelId: $connection->hotel_id,
            otaProviderId: $connection->ota_provider_id,
            connectionId: $connection->id,
            syncType: $syncType,
            direction: $direction,
            success: $success,
            requestData: $requestData,
            responseData: $responseData,
            responseCode: $responseCode,
            errorMessage: $errorMessage,
            executionTime: $executionTime
        );
    }
}
