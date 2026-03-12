<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class TransexHelper
{
    public static function getToken()
    {
        $baseUrl = env('TRANSEX_ENV') === 'staging'
            ? 'https://dev-transexpress.parallaxtec.com/api'
            : 'https://portal.transexpress.lk/api';

        $response = Http::acceptJson()
            ->withOptions(['verify' => !app()->environment('local')])
            ->post($baseUrl . '/token', [
                'username' => env('TRANSEX_USER'),
                'password' => env('TRANSEX_PASS'),
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['token'] ?? $data['data']['token'] ?? null;
        }

        throw new \Exception("Auth Failed: " . $response->status());
    }

    /**
     * Create a single order via the TransExpress single-auto-without-city endpoint.
     * Uses Bearer token authentication.
     */
    public static function createOrder($order, $deliveryService, $city)
    {
        $baseUrl = env('TRANSEX_ENV') === 'staging'
            ? 'https://dev-transexpress.parallaxtec.com/api'
            : 'https://portal.transexpress.lk/api';

        $bearerToken = env('TRANSEX_BEARER_TOKEN');

        if (empty($bearerToken)) {
            throw new \Exception('TRANSEX_BEARER_TOKEN is not configured in .env');
        }

        $payload = [
            'order_no'      => $order->id,
            'customer_name' => $order->customer->full_name,
            'address'       => $order->customer->street_address,
            'description'   => 'Order #' . $order->id,
            'phone_no'      => $order->customer->phone_number,
            'cod'           => (double) $order->total_amount,
            'note'          => $order->note ?? '',
            'city'          => $city,
        ];

        $response = Http::withToken($bearerToken)
            ->acceptJson()
            ->withOptions(['verify' => !app()->environment('local')])
            ->post($baseUrl . '/orders/upload/single-auto-without-city', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Transex API Error: ' . $response->body());
    }

    /**
     * Legacy: Create orders in bulk via the old auto-without-city endpoint.
     * Kept for backward compatibility.
     */
    public static function createOrderBulk($order, $deliveryService, $city)
    {
        $token = self::getToken();
        $baseUrl = env('TRANSEX_ENV') === 'staging'
            ? 'https://dev-transexpress.parallaxtec.com/api'
            : 'https://portal.transexpress.lk/api';

        $payload = [
            [
                'order_id'          => (string) $order->id,
                'customer_name'     => $order->customer->full_name,
                'address'           => $order->customer->street_address,
                'order_description' => 'Order #' . $order->id,
                'customer_phone'    => $order->customer->phone_number,
                'customer_phone2'   => $order->customer->phone_number_2 ?? '',
                'cod_amount'        => (double) $order->total_amount,
                'city'              => $city,
                'remarks'           => $order->note ?? '',
            ]
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->withHeaders(['service-provider' => $deliveryService])
            ->withOptions(['verify' => !app()->environment('local')])
            ->post($baseUrl . '/orders/upload/auto-without-city', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Transex API Error: ' . $response->body());
    }
}