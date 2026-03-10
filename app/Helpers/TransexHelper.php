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

    public static function createOrder($order, $deliveryService, $city)
    {
        $token = self::getToken();
        $baseUrl = env('TRANSEX_ENV') === 'staging'
            ? 'https://dev-transexpress.parallaxtec.com/api'
            : 'https://portal.transexpress.lk/api';

        // Documentation requires an ARRAY of objects []
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