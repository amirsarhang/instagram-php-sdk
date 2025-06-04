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

namespace Amirsarhang;

use GuzzleHttp\Exception\GuzzleException;

/**
 * Graph Login for Instagram PHP SDK.
 */
class InstagramGraphLogin {

    private $client;
    private $appId;
    private $appSecret;
    private $redirectUri;

    /**
     * Instantiates a new Guzzle Client
     *
     */
    public function __construct()
    {
        $this->client      = new \GuzzleHttp\Client();
        $this->appId       = $_ENV['INSTAGRAM_APP_ID'];
        $this->appSecret   = $_ENV['INSTAGRAM_APP_SECRET'];
        $this->redirectUri = $_ENV['INSTAGRAM_CALLBACK_URL'];
    }

    /**
     * Get Login Url for your Application
     *
     * @param array $permissions
     * @return string
     */
    public function getLoginUrl(array $permissions): string
    {
        $query = http_build_query([
            'client_id'            => $this->appId,
            'redirect_uri'         => $this->redirectUri,
            'scope'                => implode(',', $permissions),
            'response_type'        => 'code',
            'enable_fb_login'      => 0,
            'force_authentication' => 1
        ]);

        return 'https://www.instagram.com/oauth/authorize?' . $query;
    }

    /**
     * returns an AccessToken.
     *
     *
     * @return array|false
     *
     * @throws GuzzleException
     */
    public function getAccessToken($code)
    {
        try {
            $response = $this->client->post('https://api.instagram.com/oauth/access_token', [
                'form_params' => [
                    'client_id'     => $this->appId,
                    'client_secret' => $this->appSecret,
                    'grant_type'    => 'authorization_code',
                    'redirect_uri'  => $this->redirectUri,
                    'code'          => $code,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $access_token = $this->getLongLivedAccessToken($data['access_token']);
            $user_info = $this->getUserInfo($access_token['access_token'], 'id,name,username');

            if (@$access_token && @$user_info) {
                return array_merge($access_token, $user_info);
            }
            return false;
        } catch (\Exception $e) {
            error_log('Error retrieving access token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Long Lived Access Token
     *
     * @param string $access_token
     * @return false|mixed
     */
    public function getLongLivedAccessToken(string $access_token): mixed
    {
        try {
            $response = $this->client->get("https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=$this->appSecret&access_token=$access_token");
            $data = json_decode($response->getBody(), true);
            if (@$data) {
                return $data;
            }
            return false;
        } catch (GuzzleException $e) {
            error_log('Error retrieving Long Lived access token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * returns User Info for Connected Instagram ID
     *
     * @param string $accessToken
     * @param string $fields
     * @return array|false
     *
     * @throws GuzzleException
     */
    public function getUserInfo(string $accessToken, string $fields = ''): bool|array
    {
        if (!$fields) {
            $fields = 'user_id,name,biography,username,followers_count,follows_count,profile_picture_url,website';
        }

        try {
            $response = $this->client->get('https://graph.instagram.com/me', [
                'query' => [
                    'fields'       => $fields,
                    'access_token' => $accessToken,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            error_log('Error retrieving user info: ' . $e->getMessage());
            return false;
        }
    }
}
