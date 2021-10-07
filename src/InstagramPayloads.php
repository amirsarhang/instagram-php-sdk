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
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function postPayload(array $params, string $endpoint, string $token): array
    {
        try {
            $response = $this->fb->post(
                $endpoint,
                $params,
                $token
            );
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: '.json_encode($e->getResponseData());
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        // Return result
        return $response->getGraphNode()->asArray();
    }

    /**
     * Login and Authenticate to Instagram Graph API.
     *
     * @param string $endpoint
     * @param string $token
     * @param bool|null $graphEdge
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getPayload(string $endpoint, string $token, bool $graphEdge = null): array
    {
        try {
            $response = $this->fb->get(
                $endpoint,
                $token
            );
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: '.json_encode($e->getResponseData());
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }

        if ($graphEdge) {
            return $response->getGraphEdge()->asArray();
        }

        return $response->getGraphNode()->asArray();
    }

    /**
     * Send DELETE request to Instagram Graph API.
     *
     * @param array $params
     * @param string $endpoint
     * @param string $token
     * @return array
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function deletePayload(array $params, string $endpoint, string $token): array
    {
        try {
            $response = $this->fb->delete(
                $endpoint,
                $params,
                $token
            );
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: '.json_encode($e->getResponseData());
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        // Return result
        return $response->getGraphNode()->asArray();
    }
}
