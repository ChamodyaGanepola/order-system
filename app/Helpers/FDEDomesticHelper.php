<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class FDEDomesticHelper
{
    protected static function apiEndpoint()
    {
        return 'https://www.fdedomestic.com/api/parcel/new_api_v1.php'; // use new endpoint
    }

    /**
     * Create a new order on FDE Domestic
     */
    public static function createOrder($order, $parcelWeight = 1, $exchange = 0)
    {
        $clientId = env('FDE_CLIENT_ID');
        $apiKey   = env('FDE_API_KEY');

        if (!$clientId || !$apiKey) {
            throw new \Exception('FDE Domestic credentials not set in .env');
        }

        $postData = [
            'client_id'          => $clientId,
            'api_key'            => $apiKey,
            'order_id'           => $order->id,
            'parcel_weight'      => $parcelWeight,
            'parcel_description' => 'Order #' . $order->id,
            'recipient_name'     => $order->customer->full_name,
            'recipient_contact_1'=> preg_replace('/\D/', '', $order->customer->phone_number),
            'recipient_contact_2'=> isset($order->customer->phone_number_2) ? preg_replace('/\D/', '', $order->customer->phone_number_2) : '',
            'recipient_address'  => $order->customer->street_address,
            'recipient_city'     => $order->shipping_city ?? 'Unknown',
            'amount'             => $order->total_amount,
            'exchange'           => $exchange,
        ];

        $response = Http::asForm()->post(self::apiEndpoint(), $postData);

        if ($response->successful()) {
            $json = $response->json();
            if (isset($json['waybill_no'])) {
                return $json; // contains 'status' and 'waybill_no'
            }
            throw new \Exception('FDE Domestic did not return a waybill number.');
        }

        throw new \Exception('FDE Domestic API Error: ' . $response->body());
    }
}
