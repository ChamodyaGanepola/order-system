<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransexHelper
{
    // ──────────────────────────────────────────────────────────────
    //  Internal helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Get the base URL from config.
     */
    private static function baseUrl(): string
    {
        return rtrim(config('transex.base_url'), '/');
    }

    /**
     * Build the common order payload fields from an Order model.
     *
     * @param  \App\Models\Order  $order
     * @param  array              $extras  Additional/override fields (e.g. waybill_id, city_id, city)
     * @return array
     */
    private static function buildOrderPayload($order, array $extras = []): array
    {
        $base = [
            'order_no'      => (string) $order->id,
            'customer_name' => $order->customer->full_name,
            'address'       => $order->customer->street_address,
            'description'   => 'Order #' . $order->id,
            'phone_no'      => $order->customer->phone_number,
            'phone_no2'     => $order->customer->phone_number_2 ?? '',
            'cod'           => (double) $order->total_amount,
            'note'          => $order->note ?? '',
        ];

        return array_merge($base, $extras);
    }

    /**
     * Send a POST request to the Transex API.
     *
     * Handles authentication (Bearer API Key), headers, SSL, and
     * error‑only logging on failure.
     *
     * @param  string       $endpoint         Relative path, e.g. "/orders/upload/single-auto"
     * @param  mixed        $payload          Request body (array or nested array)
     * @param  string|null  $serviceProvider  Value for the "service-provider" header (optional)
     * @return array                          Decoded JSON response
     *
     * @throws \Exception  On non‑2xx response or transport error
     */
    private static function makeRequest(string $endpoint, $payload, ?string $serviceProvider = null): array
    {
        $url = self::baseUrl() . $endpoint;

        $headers = [];
        if ($serviceProvider) {
            $headers['service-provider'] = $serviceProvider;
        }

        try {
            $response = Http::withToken(config('transex.api_key'))
                ->acceptJson()
                ->contentType('application/json')
                ->withHeaders($headers)
                ->withOptions(['verify' => !app()->environment('local')])
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            // Log non-2xx responses
            Log::error('Transex API error', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \Exception(
                "Transex API [{$response->status()}]: " . $response->body()
            );
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Transex API connection failed', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);

            throw new \Exception('Transex API connection failed: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  Public API methods
    // ──────────────────────────────────────────────────────────────

    /**
     * Create order via "Single Auto waybill without city".
     *
     * POST /orders/upload/single-auto-without-city
     * Payload: { ... }   ← single flat object
     * Auth: Bearer API Key
     *
     * This is the DEFAULT method called from the order status flow.
     *
     * Response: { "success": "...", "orders": { "waybill_id": "BB949711", ... } }
     *
     * @param  \App\Models\Order  $order
     * @param  string             $city   City name (string)
     * @return array
     */
    public static function createOrder($order, string $city): array
    {
        $payload = self::buildOrderPayload($order, ['city' => $city]);

        return self::makeRequest('/orders/upload/single-auto-without-city', $payload);
    }

    /**
     * Add Single Order – Manual waybill.
     *
     * POST /orders/upload/single-manual
     * Requires: service-provider header, waybill_id (8 chars), city_id (string)
     *
     * @param  \App\Models\Order  $order
     * @param  string             $deliveryService  e.g. "larocher"
     * @param  string             $waybillId        Exactly 8 characters
     * @param  string             $cityId           City name as string for this endpoint
     * @return array
     */
    public static function createOrderSingleManual(
        $order,
        string $deliveryService,
        string $waybillId,
        string $cityId
    ): array {
        $payload = self::buildOrderPayload($order, [
            'waybill_id' => $waybillId,
            'city_id'    => $cityId,
        ]);

        return self::makeRequest(
            '/orders/upload/single-manual',
            $payload,
            $deliveryService
        );
    }

    /**
     * Add Single Order – Auto waybill.
     *
     * POST /orders/upload/single-auto
     * Requires: city_id (integer)
     * No service-provider header needed.
     *
     * @param  \App\Models\Order  $order
     * @param  int                $cityId  Numeric city ID
     * @return array
     */
    public static function createOrderSingleAuto($order, int $cityId): array
    {
        $payload = self::buildOrderPayload($order, [
            'city_id' => $cityId,
        ]);

        return self::makeRequest(
            '/orders/upload/single-auto',
            $payload
        );
    }

    /**
     * Add Single Order – Manual waybill without city.
     *
     * POST /orders/upload/single-manual-without-city
     * Requires: service-provider header, waybill_id (8 chars), city (string name)
     *
     * @param  \App\Models\Order  $order
     * @param  string             $deliveryService  e.g. "larocher"
     * @param  string             $waybillId        Exactly 8 characters
     * @param  string             $city             City name (string)
     * @return array
     */
    public static function createOrderSingleManualWithoutCity(
        $order,
        string $deliveryService,
        string $waybillId,
        string $city
    ): array {
        $payload = self::buildOrderPayload($order, [
            'waybill_id' => $waybillId,
            'city'       => $city,
        ]);

        return self::makeRequest(
            '/orders/upload/single-manual-without-city',
            $payload,
            $deliveryService
        );
    }
}