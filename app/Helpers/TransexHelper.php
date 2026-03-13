<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class TransexHelper
{
    /**
     * Get a Bearer token for staging.
     * In production, use API Key directly.
     */
    public static function getBearerToken()
    {
        // Use staging login only if TRANSEX_ENV=staging
        if (env('TRANSEX_ENV') === 'staging') {
            $baseUrl = 'https://portal.transexpress.lk/api';

            $response = Http::acceptJson()
                ->withOptions(['verify' => ! app()->environment('local')])
                ->post($baseUrl . '/login/client', [
                    'email'    => env('TRANSEX_USER'),
                    'password' => env('TRANSEX_PASS'),
                ]);

            if ($response->successful() && isset($response['token'])) {
                return $response['token'];
            }

            throw new \Exception('Transex Login Failed: ' . $response->body());
        }

        // For production, use API Key directly from env
        return env('TRANSEX_API_KEY');
    }

    /**
     * Create a single order.
     */
    public static function createOrder($order, $deliveryService, $city)
    {
        $baseUrl = env('TRANSEX_ENV') === 'staging'
            ? 'https://portal.transexpress.lk/api'
            : 'https://portal.transexpress.lk/api';

        $bearerToken = self::getBearerToken();

        $payload = [
            'order_no'      => $order->id,
            'customer_name' => $order->customer->full_name,
            'address'       => $order->customer->street_address,
            'description'   => 'Order #' . $order->id,
            'phone_no'      => (string) preg_replace('/\D/', '', $order->customer->phone_number),
            'phone_no2'     => isset($order->customer->phone_number_2)
                ? (string) preg_replace('/\D/', '', $order->customer->phone_number_2)
                : '',
            'cod'           => (double) $order->total_amount,
            'note'          => $order->note ?? '',
            'city'          => $city,
        ];

        $response = Http::withToken($bearerToken)
            ->acceptJson()
            ->withOptions(['verify' => ! app()->environment('local')])
            ->post($baseUrl . '/orders/upload/single-auto-without-city', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Transex API Exception: ' . $response->body());
    }
}
