<?php

namespace ShahariarAhmad\CourierFraudCheckerBd\Services;
use Illuminate\Support\Facades\Http;

class PathaoService
{
    public function pathao($phoneNumber)
    {
        $response = Http::post('https://merchant.pathao.com/api/v1/login', [
            'username' => env('PATHAO_USER'),
            'password' => env('PATHAO_PASSWORD'),
        ]);

        // Check if the response is successful
        if ($response->successful()) {
            $data = $response->json(); // Assuming JSON response
            $accessToken = trim($data['access_token']);

            $responseAuth = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post('https://merchant.pathao.com/api/v1/user/success', [
                'phone' => $phoneNumber,  // Replace with actual data required by the API
            ]);

            if ($responseAuth->successful()) {
                $object = $responseAuth->json();
            } else {
                return $responseAuth->status();
            }

            return [
                'success' => $object['data']['customer']['successful_delivery'] ?? 0,
                'cancel' =>  ($object['data']['customer']['total_delivery'] ?? 0 )- ($object['data']['customer']['successful_delivery'] ?? 0),
                'total' => $object['data']['customer']['total_delivery'] ?? 0,
            ];
        }
    }
}
