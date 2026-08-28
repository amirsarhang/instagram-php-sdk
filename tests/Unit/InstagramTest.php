<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit;

use Amirsarhang\Auth\OAuth;
use Amirsarhang\Config;
use Amirsarhang\Http\GraphClient;
use Amirsarhang\Instagram;
use Amirsarhang\Resources\Account;
use Amirsarhang\Resources\Comments;
use Amirsarhang\Resources\Messages;
use Amirsarhang\Resources\Webhooks;
use Amirsarhang\Tests\TestCase;

final class InstagramTest extends TestCase
{
    public function testItExposesTheResourceGroups(): void
    {
        $instagram = $this->instagram();

        $this->assertInstanceOf(Comments::class, $instagram->comments());
        $this->assertInstanceOf(Messages::class, $instagram->messages());
        $this->assertInstanceOf(Webhooks::class, $instagram->webhooks());
        $this->assertInstanceOf(Account::class, $instagram->account());
        $this->assertInstanceOf(OAuth::class, $instagram->oauth());
        $this->assertInstanceOf(GraphClient::class, $instagram->graph());
    }

    public function testItBuildsTheClientFromTheConfiguredGraphVersion(): void
    {
        $this->http->queue($this->json([]));

        $instagram = new Instagram(self::TOKEN, new Config(graphVersion: 'v19.0'), $this->http);
        $instagram->get('/me');

        $this->assertSame('https://graph.instagram.com/v19.0/me', $this->url());
    }

    public function testItReadsTheConfigurationFromTheEnvironmentByDefault(): void
    {
        $this->forgetEnvironment('INSTAGRAM_GRAPH_VERSION');

        $instagram = new Instagram(self::TOKEN, null, $this->http);

        $this->assertSame(Config::DEFAULT_GRAPH_VERSION, $instagram->config()->graphVersion());
    }

    public function testGetSendsTheEndpointWithTheAccessToken(): void
    {
        $this->http->queue($this->json(['id' => '17841400000000000']));

        $result = $this->instagram()->get('/me', ['fields' => 'id,name']);

        $this->assertSame(['id' => '17841400000000000'], $result);
        $this->assertSame('GET', $this->request()->getMethod());
        $this->assertSame(self::BASE_URI . 'me', $this->url());
        $this->assertSame(['fields' => 'id,name'], $this->query());
        $this->assertSame('Bearer ' . self::TOKEN, $this->request()->getHeaderLine('Authorization'));
    }

    public function testPostSendsTheParameters(): void
    {
        $this->http->queue($this->json(['id' => '1']));

        $this->instagram()->post('/me/messages', ['message' => 'Hello']);

        $this->assertSame('POST', $this->request()->getMethod());
        $this->assertSame(['message' => 'Hello'], $this->requestBody());
    }

    public function testDeleteSendsTheParameters(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->instagram()->delete('/17900000000000000');

        $this->assertSame('DELETE', $this->request()->getMethod());
        $this->assertSame(self::BASE_URI . '17900000000000000', $this->url());
    }

    public function testEveryResourceSharesOneClient(): void
    {
        $instagram = $this->instagram();

        $this->assertSame($instagram->graph(), $instagram->graph());
    }

    public function testWithTokenReturnsACopyLeavingTheOriginalAlone(): void
    {
        $this->http->queue($this->json([]), $this->json([]));

        $instagram = $this->instagram();
        $other = $instagram->withToken('another-token');

        $other->get('/me');
        $instagram->get('/me');

        $this->assertNotSame($instagram, $other);
        $this->assertSame('Bearer another-token', $this->request(0)->getHeaderLine('Authorization'));
        $this->assertSame('Bearer ' . self::TOKEN, $this->request(1)->getHeaderLine('Authorization'));
    }

    public function testWithTokenKeepsTheConfiguration(): void
    {
        $instagram = $this->instagram();

        $this->assertSame($instagram->config(), $instagram->withToken('another-token')->config());
    }

    private function instagram(): Instagram
    {
        return new Instagram(self::TOKEN, $this->config(), $this->http);
    }
}
