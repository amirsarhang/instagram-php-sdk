<?php

/**
 * This file is part of the amirsarhang/instagram-php-sdk library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Amirhossein Sarhangian <ah.sarhangian@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Amirsarhang;

use GuzzleHttp\Exception\GuzzleException;

/**
 * HTTP Payloads for Instagram PHP SDK.
 */
class InstagramPayloads extends Instagram
{
    /**
     * Send POST request to Instagram Graph API.
     *
     * @param array $params
     * @param string $endpoint
     * @param string $token
     * @return array
     *
     */
    public function postPayload(array $params, string $endpoint, string $token): array
    {
        try {
            $response = $this->client->post(
                $endpoint,
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $params,
                ]
            );

            $body = $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            echo 'Graph returned an error: '.$e->getMessage();
            exit;
        }

        return json_decode($body, true);
    }

    /**
     * Login and Authenticate to Instagram Graph API.
     *
     * @param string $endpoint
     * @param string $token
     * @return array
     *
     */
    public function getPayload(string $endpoint, string $token): array
    {
        try {
            $response = $this->client->get(
                $endpoint,
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                        'Accept' => 'application/json',
                    ],
                ]
            );

            $body = $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            echo 'Graph returned an error: '.$e->getMessage();
            exit;
        }

        return json_decode($body, true);
    }

    /**
     * Send DELETE request to Instagram Graph API.
     *
     * @param array $params
     * @param string $endpoint
     * @param string $token
     * @return array
     *
     */
    public function deletePayload(array $params, string $endpoint, string $token): array
    {
        try {
            $response = $this->client->delete(
                $endpoint,
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $params,
                ]
            );

            $body = $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        }

        return json_decode($body, true);
    }
}
