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

namespace Amirsarhang\Auth;

use Amirsarhang\Config;
use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\InvalidArgumentException;
use Amirsarhang\Http\GraphClient;

/**
 * The Instagram Business Login flow.
 *
 * @link https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/business-login
 */
final class OAuth
{
    public const AUTHORIZE_URL = 'https://www.instagram.com/oauth/authorize';

    public const ACCESS_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

    public const REFRESH_URL = 'https://graph.instagram.com/refresh_access_token';

    public const EXCHANGE_URL = 'https://graph.instagram.com/access_token';

    public function __construct(
        private Config $config,
        private GraphClient $client
    ) {
    }

    /**
     * The URL to send the user to so they can grant your app access.
     *
     * @param array<int, string> $permissions
     *
     * @throws InvalidArgumentException
     */
    public function loginUrl(array $permissions): string
    {
        if ($permissions === []) {
            throw InvalidArgumentException::empty('permissions', __METHOD__);
        }

        return self::AUTHORIZE_URL . '?' . http_build_query([
            'client_id' => $this->config->appId(),
            'redirect_uri' => $this->config->redirectUri(),
            'scope' => implode(',', $permissions),
            'response_type' => 'code',
            'enable_fb_login' => 0,
            'force_authentication' => 1,
        ]);
    }

    /**
     * Trade the code from your callback URL for a short lived access token.
     *
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function exchangeCode(string $code): array
    {
        if ($code === '') {
            throw InvalidArgumentException::empty('code', __METHOD__);
        }

        return $this->client->postForm(self::ACCESS_TOKEN_URL, [
            'client_id' => $this->config->appId(),
            'client_secret' => $this->config->appSecret(),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->config->redirectUri(),
            'code' => $code,
        ]);
    }

    /**
     * Trade a short lived token for one that lasts about 60 days.
     *
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function longLivedToken(string $accessToken): array
    {
        if ($accessToken === '') {
            throw InvalidArgumentException::empty('accessToken', __METHOD__);
        }

        return $this->client->get(self::EXCHANGE_URL, [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => $this->config->appSecret(),
            'access_token' => $accessToken,
        ]);
    }

    /**
     * Push a long lived token's expiry back by another 60 days.
     *
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function refreshToken(string $accessToken): array
    {
        if ($accessToken === '') {
            throw InvalidArgumentException::empty('accessToken', __METHOD__);
        }

        return $this->client->get(self::REFRESH_URL, [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $accessToken,
        ]);
    }

    /**
     * Run the whole callback flow: code to long lived token, plus the account it belongs to.
     *
     * @param array<int, string> $fields The account fields to return alongside the token.
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function connect(string $code, array $fields = ['id', 'name', 'username']): array
    {
        $shortLived = $this->exchangeCode($code);

        if (!isset($shortLived['access_token']) || !is_string($shortLived['access_token'])) {
            throw GraphException::missingAccessToken();
        }

        $token = $this->longLivedToken($shortLived['access_token']);

        if (!isset($token['access_token']) || !is_string($token['access_token'])) {
            throw GraphException::missingAccessToken();
        }

        $account = $this->client->withToken($token['access_token'])
            ->get('/me', ['fields' => implode(',', $fields)]);

        return array_merge($token, $account);
    }
}
