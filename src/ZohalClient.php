<?php

namespace ZohalAPI;

use ZohalAPI\Exceptions\RequestException;

class ZohalClient
{
    private $baseUrl = 'https://service.zohal.io/api/v0';
    private $token;

    public function __construct($bearerToken)
    {
        $this->token = $bearerToken;
    }

    /**
     * Card Owner Inquiry
     *
     * @param string $cardNumber
     * @return array
     */
    public function cardInquiry($cardNumber)
    {
        return $this->post('/services/inquiry/card_inquiry', array(
            'card_number' => $cardNumber
        ));
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws RequestException
     */
    private function post($endpoint, $data)
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ),
            CURLOPT_POSTFIELDS => json_encode($data),
        ));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RequestException($error);
        }

        $result = json_decode($response, true);
        curl_close($ch);

        if (!is_array($result) || !isset($result['result']) || $result['result'] != 1) {
            $error = isset($result['response_body']['error_code'])
                ? $result['response_body']['error_code']
                : 'UNKNOWN_ERROR';

            $message = isset($result['response_body']['message'])
                ? $result['response_body']['message']
                : 'Unknown error';

            throw new RequestException($error . ' : ' . $message);
        }

        return isset($result['response_body']['data'])
            ? $result['response_body']['data']
            : array();
    }
}
