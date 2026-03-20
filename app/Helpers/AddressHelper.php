<?php
namespace App\Helpers;

class AddressHelper
{
    protected static $cityToDistrict = [
        'colombo'         => 'Colombo',
        'dehiwala'        => 'Colombo',
        'mount lavinia'   => 'Colombo',
        'borella'         => 'Colombo',
        'nugegoda'        => 'Colombo',
        'piliyandala'     => 'Colombo',
        'homagama'        => 'Colombo',
        'maharagama'      => 'Colombo',
        'kesbewa'         => 'Colombo',
        'padukka'         => 'Colombo',
        'negombo'         => 'Gampaha',
        'wattala'         => 'Gampaha',
        'ja‑ela'          => 'Gampaha',
        'gampaha'         => 'Gampaha',
        'kadawatha'       => 'Gampaha',
        'yakkala'         => 'Gampaha',
        'nittambuwa'      => 'Gampaha',
        'kalutara'        => 'Kalutara',
        'horana'          => 'Kalutara',
        'panadura'        => 'Kalutara',
        'bandaragama'     => 'Kalutara',
        'kandy'           => 'Kandy',
        'peradeniya'      => 'Kandy',
        'matale'          => 'Matale',
        'nuwara eliya'    => 'Nuwara Eliya',
        'hatton'          => 'Nuwara Eliya',
        'galle'           => 'Galle',
        'hikkaduwa'       => 'Galle',
        'matara'          => 'Matara',
        'weligama'        => 'Matara',
        'hambantota'      => 'Hambantota',
        'tangalle'        => 'Hambantota',
        'jaffna'          => 'Jaffna',
        'kilinochchi'     => 'Kilinochchi',
        'mannar'          => 'Mannar',
        'vavuniya'        => 'Vavuniya',
        'mullaitivu'      => 'Mullaitivu',
        'trincomalee'     => 'Trincomalee',
        'batticaloa'      => 'Batticaloa',
        'ampara'          => 'Ampara',
        'kalmunai'        => 'Ampara',
        'akkaraipattu'    => 'Ampara',
        'kurunegala'      => 'Kurunegala',
        'puttalam'        => 'Puttalam',
        'chilaw'          => 'Puttalam',
        'kuliyapitiya'    => 'Kurunegala',
        'anuradhapura'    => 'Anuradhapura',
        'polonnaruwa'     => 'Polonnaruwa',
        'badulla'         => 'Badulla',
        'moneragala'      => 'Moneragala',
        'ratnapura'       => 'Ratnapura',
        'kegalle'         => 'Kegalle',
    ];

    protected static $districtToProvince = [
        'Colombo'       => 'Western',
        'Gampaha'       => 'Western',
        'Kalutara'      => 'Western',
        'Kandy'         => 'Central',
        'Matale'        => 'Central',
        'Nuwara Eliya'  => 'Central',
        'Galle'         => 'Southern',
        'Matara'        => 'Southern',
        'Hambantota'    => 'Southern',
        'Jaffna'        => 'Northern',
        'Kilinochchi'   => 'Northern',
        'Mannar'        => 'Northern',
        'Vavuniya'      => 'Northern',
        'Mullaitivu'    => 'Northern',
        'Trincomalee'   => 'Eastern',
        'Batticaloa'    => 'Eastern',
        'Ampara'        => 'Eastern',
        'Kurunegala'    => 'North Western',
        'Puttalam'      => 'North Western',
        'Anuradhapura'  => 'North Central',
        'Polonnaruwa'   => 'North Central',
        'Badulla'       => 'Uva',
        'Moneragala'    => 'Uva',
        'Ratnapura'     => 'Sabaragamuwa',
        'Kegalle'       => 'Sabaragamuwa',
    ];

    public static function parseAddress($fullAddress)
    {
        $parts = array_map('trim', explode(',', $fullAddress));

        $cityInput = isset($parts[2]) ? strtolower(trim($parts[2])) : null;

        $district = null;
        $province = null;

        if ($cityInput && isset(self::$cityToDistrict[$cityInput])) {
            $district = self::$cityToDistrict[$cityInput];
            $province = self::$districtToProvince[$district] ?? null;
        }

        return [
            'street_address' => $fullAddress,
            'city'           => $cityInput,
            'district'       => $district,
            'province'       => $province,
        ];
    }
}
