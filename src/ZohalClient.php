<?php

namespace ZohalAPI;

use ZohalAPI\Exceptions\RequestException;

class ZohalClient
{
    private string $baseUrl = 'https://service.zohal.io/api/v0';
    private string $token;

    public function __construct(string $bearerToken)
    {
        $this->token = $bearerToken;
    }

    /**
     * Card Owner Inquiry
     */
    public function cardInquiry(string $cardNumber): array
    {
        return $this->post('/services/inquiry/card_inquiry', [
            'card_number' => $cardNumber
        ]);
    }

    private function post(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RequestException(curl_error($ch));
        }

        $result = json_decode($response, true);
        curl_close($ch);

        if (!isset($result['result']) || $result['result'] !== 1) {
            $error = $result['response_body']['error_code'] ?? 'UNKNOWN_ERROR';
            $message = $result['response_body']['message'] ?? 'Unknown error';
            throw new RequestException($error . ' : ' . $message);
        }

        return $result['response_body']['data'] ?? [];
    }
}