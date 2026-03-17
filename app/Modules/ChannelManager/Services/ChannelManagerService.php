<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Services;

use App\Modules\ChannelManager\Models\OtaProvider;
use App\Modules\ChannelManager\Models\HotelOtaConnection;
use App\Modules\ChannelManager\Models\OtaSyncQueue;
use App\Modules\ChannelManager\Models\OtaSyncLog;
use Carbon\Carbon;

/**
 * Channel Manager Service
 * 
 * Main service for managing OTA integrations.
 */
class ChannelManagerService
{
    /**
     * Connect hotel to OTA provider.
     */
    public function connectHotelToOta(
        int $hotelId,
        int $otaProviderId,
        array $credentials,
        array $settings = []
    ): HotelOtaConnection {
        return HotelOtaConnection::updateOrCreate(
            [
                'hotel_id' => $hotelId,
                'ota_provider_id' => $otaProviderId,
            ],
            [
                'credentials' => $credentials,
                'settings' => $settings,
                'status' => 'pending',
            ]
        );
    }
    
    /**
     * Disconnect hotel from OTA.
     */
    public function disconnectHotelFromOta(int $hotelId, int $otaProviderId): bool
    {
        $connection = HotelOtaConnection::where('hotel_id', $hotelId)
            ->where('ota_provider_id', $otaProviderId)
            ->first();
        
        if ($connection) {
            $connection->update(['status' => 'suspended']);
            return true;
        }
        
        return false;
    }
    
    /**
     * Queue availability sync for hotel.
     */
    public function queueAvailabilitySync(
        int $hotelId,
        ?int $otaProviderId = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): void {
        $providers = $otaProviderId
            ? [OtaProvider::find($otaProviderId)]
            : OtaProvider::where('is_active', true)
                ->where('supports_push', true)
                ->get();
        
        foreach ($providers as $provider) {
            OtaSyncQueue::create([
                'hotel_id' => $hotelId,
                'ota_provider_id' => $provider->id,
                'sync_type' => 'availability',
                'payload' => [
                    'start_date' => $startDate?->toDateString() ?? now()->toDateString(),
                    'end_date' => $endDate?->toDateString() ?? now()->addDays(365)->toDateString(),
                ],
                'status' => 'pending',
            ]);
        }
    }
    
    /**
     * Queue rate sync for hotel.
     */
    public function queueRateSync(
        int $hotelId,
        ?int $otaProviderId = null,
        ?int $roomTypeId = null,
        ?int $ratePlanId = null
    ): void {
        $providers = $otaProviderId
            ? [OtaProvider::find($otaProviderId)]
            : OtaProvider::where('is_active', true)
                ->where('supports_push', true)
                ->get();
        
        foreach ($providers as $provider) {
            OtaSyncQueue::create([
                'hotel_id' => $hotelId,
                'ota_provider_id' => $provider->id,
                'sync_type' => 'rates',
                'payload' => [
                    'room_type_id' => $roomTypeId,
                    'rate_plan_id' => $ratePlanId,
                ],
                'status' => 'pending',
            ]);
        }
    }
    
    /**
     * Log sync operation.
     */
    public function logSync(
        int $hotelId,
        int $otaProviderId,
        string $syncType,
        string $direction,
        bool $success,
        ?array $requestData = null,
        ?array $responseData = null,
        ?int $responseCode = null,
        ?string $errorMessage = null,
        ?float $executionTime = null,
        ?int $connectionId = null
    ): OtaSyncLog {
        return OtaSyncLog::create([
            'hotel_id' => $hotelId,
            'ota_provider_id' => $otaProviderId,
            'connection_id' => $connectionId,
            'sync_type' => $syncType,
            'direction' => $direction,
            'success' => $success,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'response_code' => $responseCode,
            'error_message' => $errorMessage,
            'execution_time' => $executionTime,
            'synced_at' => now(),
        ]);
    }
    
    /**
     * Get sync statistics for hotel.
     */
    public function getSyncStats(int $hotelId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = OtaSyncLog::where('hotel_id', $hotelId);
        
        if ($startDate) {
            $query->where('synced_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('synced_at', '<=', $endDate);
        }
        
        $total = $query->count();
        $success = (clone $query)->where('success', true)->count();
        $failed = (clone $query)->where('success', false)->count();
        
        $avgExecutionTime = (clone $query)->avg('execution_time');
        
        $byProvider = [];
        $providers = OtaProvider::all();
        
        foreach ($providers as $provider) {
            $providerLogs = (clone $query)->where('ota_provider_id', $provider->id);
            $byProvider[$provider->code] = [
                'name' => $provider->name,
                'total' => $providerLogs->count(),
                'success' => $providerLogs->where('success', true)->count(),
                'failed' => $providerLogs->where('success', false)->count(),
            ];
        }
        
        return [
            'total_syncs' => $total,
            'success' => $success,
            'failed' => $failed,
            'success_rate' => $total > 0 ? ($success / $total) * 100 : 0,
            'avg_execution_time' => $avgExecutionTime,
            'by_provider' => $byProvider,
        ];
    }
    
    /**
     * Get pending sync jobs count.
     */
    public function getPendingSyncsCount(int $hotelId): int
    {
        return OtaSyncQueue::where('hotel_id', $hotelId)
            ->where('status', 'pending')
            ->count();
    }
    
    /**
     * Get active connections for hotel.
     */
    public function getActiveConnections(int $hotelId)
    {
        return HotelOtaConnection::where('hotel_id', $hotelId)
            ->where('status', 'active')
            ->with('otaProvider')
            ->get();
    }
}
