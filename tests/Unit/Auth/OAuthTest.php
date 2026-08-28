<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit\Auth;

use Amirsarhang\Auth\OAuth;
use Amirsarhang\Config;
use Amirsarhang\Exception\ConfigurationException;
use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\InvalidArgumentException;
use Amirsarhang\Tests\TestCase;

final class OAuthTest extends TestCase
{
    public function testLoginUrlCarriesTheConfiguredApplication(): void
    {
        $url = $this->oauth()->loginUrl(['instagram_business_basic']);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->assertStringStartsWith(OAuth::AUTHORIZE_URL . '?', $url);
        $this->assertSame([
            'client_id' => '1234567890',
            'redirect_uri' => 'https://example.com/instagram/callback',
            'scope' => 'instagram_business_basic',
            'response_type' => 'code',
            'enable_fb_login' => '0',
            'force_authentication' => '1',
        ], $query);
    }

    public function testLoginUrlJoinsPermissionsWithCommas(): void
    {
        $url = $this->oauth()->loginUrl(['instagram_business_basic', 'instagram_business_manage_messages']);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame('instagram_business_basic,instagram_business_manage_messages', $query['scope']);
    }

    public function testLoginUrlRejectsAnEmptyPermissionList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires a non-empty $permissions');

        $this->oauth()->loginUrl([]);
    }

    public function testLoginUrlNeedsTheApplicationCredentials(): void
    {
        $oauth = new OAuth(new Config(), $this->graphClient());

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Missing "INSTAGRAM_APP_ID" configuration');

        $oauth->loginUrl(['instagram_business_basic']);
    }

    public function testExchangeCodePostsTheCallbackCodeAsAForm(): void
    {
        $this->http->queue($this->json(['access_token' => 'short-lived', 'user_id' => 17841400000000000]));

        $result = $this->oauth()->exchangeCode('callback-code');

        $this->assertSame(['access_token' => 'short-lived', 'user_id' => 17841400000000000], $result);
        $this->assertSame('POST', $this->request()->getMethod());
        $this->assertSame(OAuth::ACCESS_TOKEN_URL, $this->url());
        $this->assertSame([
            'client_id' => '1234567890',
            'client_secret' => 'app-secret',
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'https://example.com/instagram/callback',
            'code' => 'callback-code',
        ], $this->formBody());
    }

    public function testExchangeCodeRejectsAnEmptyCode(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->oauth()->exchangeCode('');
    }

    public function testLongLivedTokenExchangesTheShortLivedOne(): void
    {
        $this->http->queue($this->json(['access_token' => 'long-lived', 'expires_in' => 5183944]));

        $result = $this->oauth()->longLivedToken('short-lived');

        $this->assertSame(['access_token' => 'long-lived', 'expires_in' => 5183944], $result);
        $this->assertSame(OAuth::EXCHANGE_URL, $this->url());
        $this->assertSame([
            'grant_type' => 'ig_exchange_token',
            'client_secret' => 'app-secret',
            'access_token' => 'short-lived',
        ], $this->query());
    }

    public function testRefreshTokenPushesTheExpiryBack(): void
    {
        $this->http->queue($this->json(['access_token' => 'refreshed', 'expires_in' => 5183944]));

        $result = $this->oauth()->refreshToken('long-lived');

        $this->assertSame(['access_token' => 'refreshed', 'expires_in' => 5183944], $result);
        $this->assertSame(OAuth::REFRESH_URL, $this->url());
        $this->assertSame([
            'grant_type' => 'ig_refresh_token',
            'access_token' => 'long-lived',
        ], $this->query());
    }

    public function testRefreshTokenRejectsAnEmptyToken(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->oauth()->refreshToken('');
    }

    public function testConnectMergesTheLongLivedTokenWithTheAccountItBelongsTo(): void
    {
        $this->http->queue(
            $this->json(['access_token' => 'short-lived']),
            $this->json(['access_token' => 'long-lived', 'token_type' => 'bearer', 'expires_in' => 5183944]),
            $this->json(['id' => '1234567890123456', 'name' => 'Test Page', 'username' => 'test_page'])
        );

        $result = $this->oauth()->connect('callback-code');

        $this->assertSame([
            'access_token' => 'long-lived',
            'token_type' => 'bearer',
            'expires_in' => 5183944,
            'id' => '1234567890123456',
            'name' => 'Test Page',
            'username' => 'test_page',
        ], $result);
    }

    public function testConnectRequestsTheAccountWithTheLongLivedToken(): void
    {
        $this->http->queue(
            $this->json(['access_token' => 'short-lived']),
            $this->json(['access_token' => 'long-lived']),
            $this->json(['id' => '1234567890123456'])
        );

        $this->oauth()->connect('callback-code');

        $this->assertSame(self::BASE_URI . 'me', $this->url(2));
        $this->assertSame(['fields' => 'id,name,username'], $this->query(2));
        $this->assertSame('Bearer long-lived', $this->request(2)->getHeaderLine('Authorization'));
    }

    public function testConnectAcceptsCustomAccountFields(): void
    {
        $this->http->queue(
            $this->json(['access_token' => 'short-lived']),
            $this->json(['access_token' => 'long-lived']),
            $this->json(['username' => 'test_page'])
        );

        $this->oauth()->connect('callback-code', ['username', 'followers_count']);

        $this->assertSame(['fields' => 'username,followers_count'], $this->query(2));
    }

    public function testConnectFailsWhenInstagramRejectsTheCode(): void
    {
        $this->http->queue($this->json([
            'error' => ['message' => 'This authorization code has expired.', 'type' => 'OAuthException'],
        ], 400));

        $this->expectException(GraphException::class);
        $this->expectExceptionMessage('This authorization code has expired.');

        $this->oauth()->connect('expired-code');
    }

    public function testConnectFailsWhenNoTokenComesBack(): void
    {
        $this->http->queue($this->json(['user_id' => 17841400000000000]));

        $this->expectException(GraphException::class);
        $this->expectExceptionMessage('Instagram did not return an access token.');

        $this->oauth()->connect('callback-code');
    }

    public function testConnectStopsWhenTheExchangeReturnsNoToken(): void
    {
        $this->http->queue(
            $this->json(['access_token' => 'short-lived']),
            $this->json(['expires_in' => 5183944])
        );

        try {
            $this->oauth()->connect('callback-code');
            $this->fail('Expected a GraphException.');
        } catch (GraphException) {
            $this->assertSame(2, $this->http->count());
        }
    }

    private function oauth(): OAuth
    {
        return new OAuth($this->config(), $this->graphClient());
    }
}
