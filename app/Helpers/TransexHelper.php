<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class TransexHelper
{
    public static function getBearerToken()
    {
        if (env('TRANSEX_ENV') === 'staging') {
            $baseUrl  = 'https://portal.transexpress.lk/api';
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

        return env('TRANSEX_API_KEY');
    }

    public static function createOrder($order, $deliveryService, $city)
    {
        $baseUrl     = 'https://portal.transexpress.lk/api';
        $bearerToken = self::getBearerToken();

        $payload = [
            'order_id'      => $order->id,
            'customer_name' => $order->customer->full_name,
            'address'       => $order->customer->street_address,
            'description'   => 'Order #' . $order->id,
            'phone_no'      => self::normalizePhone($order->customer->phone_number),
            'phone_no2'     => self::normalizePhone($order->customer->phone_number_2 ?? ''),
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

    private static function normalizePhone($phone)
    {
        // Remove all non-digits
        $phone = preg_replace('/\D/', '', $phone);

        // Convert +94 or 94 prefix to 0
        if (str_starts_with($phone, '+94')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '94')) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }
    public static function createBulkOrders(array $orders)
{
    $bearerToken = self::getBearerToken();
    $payload = array_map(function ($order) {
        return [
            'order_id'          => $order['order_id'],
            'customer_name'     => $order['customer_name'],
            'address'           => $order['address'],
            'order_description' => $order['order_description'],
            'customer_phone'    => self::normalizePhone($order['customer_phone']),
            'customer_phone2'   => self::normalizePhone($order['customer_phone2'] ?? ''),
            'cod_amount'        => (double) ($order['cod_amount'] ?? 0),
            'city'              => $order['city'],
            'remarks'           => $order['remarks'] ?? '',
        ];
    }, $orders);

    \Log::info('Transex Bulk Payload:', $payload);

    $response = Http::withToken($bearerToken)
        ->acceptJson()
        ->withOptions(['verify' => ! app()->environment('local')])
        ->post(
            'https://portal.transexpress.lk/api/orders/upload/auto-without-city',
            $payload
        );

    if ($response->successful()) {
        return $response->json();
    }

   if (! $response->successful()) {
    \Log::error('Transex Error: ' . $response->status() . ' ' . $response->body());
    throw new \Exception('Transex API Exception (bulk): ' . $response->body());
}
}
}
