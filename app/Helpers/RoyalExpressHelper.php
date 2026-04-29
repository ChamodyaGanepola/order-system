<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RoyalExpressHelper
{
    protected static function apiEndpoint()
    {
        return 'https://v1.api.curfox.com';
    }

    protected static function getTenant()
    {
        return 'royalexpress';
    }

    // =========================
    // STATES
    // =========================
    public static function getStates()
    {
        return Cache::remember('royal_states', now()->addDays(7), function () {

            $path = storage_path('app/royal/states.json');

            if (!file_exists($path)) {
                throw new \Exception('States file not found');
            }

            return json_decode(file_get_contents($path), true)['data'] ?? [];
        });
    }

    // =========================
    // CITIES
    // =========================
    public static function getCities()
    {
        return Cache::remember('royal_cities', now()->addDays(7), function () {

            $path = storage_path('app/royal/cities.json');

            if (!file_exists($path)) {
                throw new \Exception('Cities file not found');
            }

            return json_decode(file_get_contents($path), true)['data'] ?? [];
        });
    }

    // =========================
    // FIND ORIGIN (IMPORTANT FIX)
    // =========================
    public static function getDefaultOrigin()
{
    return [
        'city'  => env('ROYAL_ORIGIN_CITY'),
        'state' => env('ROYAL_ORIGIN_STATE'),
    ];
}

    // =========================
    // MATCH DESTINATION
    // =========================
    public static function matchCityAndState($customerCity, $customerDistrict)
    {
        $states = self::getStates();

        $customerCity = strtolower(trim($customerCity));
        $customerDistrict = strtolower(trim($customerDistrict));

        foreach ($states as $state) {

            $stateName = strtolower($state['name'] ?? '');

            foreach (($state['cities'] ?? []) as $city) {

                $cityName = strtolower($city['name'] ?? '');

                if ($cityName === $customerCity && $stateName === $customerDistrict) {
                    return [
                        'city'  => $city['name'],
                        'state' => $state['name'],
                    ];
                }

                if (
                    str_contains($cityName, $customerCity) ||
                    str_contains($customerCity, $cityName)
                ) {
                    return [
                        'city'  => $city['name'],
                        'state' => $state['name'],
                    ];
                }
            }
        }

        return null;
    }

    // =========================
    // LOGIN
    // =========================
    public static function login()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-tenant' => env('ROYAL_EXPRESS_TENANT'),
        ])->post(self::apiEndpoint() . '/api/public/merchant/login', [
            'email' => env('ROYAL_EXPRESS_EMAIL'),
            'password' => env('ROYAL_EXPRESS_PASSWORD'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Login failed');
        }

        $data = $response->json();

        Cache::put('royal_express_token', $data['token'], now()->addHours(1));

        return $data['token'];
    }

    protected static function getToken()
    {
        return Cache::remember('royal_express_token', now()->addHours(1), function () {
            return self::login();
        });
    }

    // =========================
    // BULK ORDER
    // =========================
    public static function createBulkOrders($orders)
    {
        $token = self::getToken();

        $orderData = [];

        foreach ($orders as $order) {

            $customer = $order->customer ?? null;

            if (!$customer) continue;

            $matched = self::matchCityAndState(
                $customer->city,
                $customer->district
            );

            if (!$matched) {
                throw new \Exception("Invalid destination city/state: {$customer->city}, {$customer->district}");
            }

            $origin = self::getDefaultOrigin();

            $orderData[] = [

                'order_no' => (string)$order->id,
                'customer_name' => $customer->full_name,
                'customer_address' => $customer->street_address,
                'customer_phone' => preg_replace('/\D/', '', $customer->phone_number),
                'customer_secondary_phone' => $customer->phone_number_2
                    ? preg_replace('/\D/', '', $customer->phone_number_2)
                    : '',
                'destination_city_name' => $matched['city'],
                'destination_state_name' => $matched['state'],
                'cod' => (int)$order->total_amount,
                'description' => 'Order #' . $order->id,
                'weight' => 1,
                'remark' => $order->note ?? '',
            ];
        }

        if (empty($orderData)) {
            throw new \Exception('No valid orders');
        }

        $payload = [
            'general_data' => [
                'merchant_business_id' => '12932',
                'origin_city_name' => $origin['city'],
                'origin_state_name' => $origin['state'], 
            ],
            'order_data' => $orderData,
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'X-tenant' => env('ROYAL_EXPRESS_TENANT'),
        ])->post(self::apiEndpoint() . '/api/public/merchant/order/bulk', $payload);

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }
}
