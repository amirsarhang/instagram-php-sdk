<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit\Resources;

use Amirsarhang\Resources\Webhooks;
use Amirsarhang\Tests\TestCase;

final class WebhooksTest extends TestCase
{
    public function testSubscribeSubscribesToMessagesByDefault(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->webhooks()->subscribe();

        $this->assertSame('POST', $this->request()->getMethod());
        $this->assertSame(self::BASE_URI . 'me/subscribed_apps', $this->url());
        $this->assertSame([
            'subscribed_fields' => 'messages',
            'access_token' => self::TOKEN,
        ], $this->query());
    }

    public function testSubscribeAcceptsCustomFields(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->webhooks()->subscribe(['messages', 'comments']);

        $this->assertSame('messages,comments', $this->query()['subscribed_fields']);
    }

    public function testUnsubscribeDeletesTheSubscription(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->webhooks()->unsubscribe();

        $this->assertSame('DELETE', $this->request()->getMethod());
        $this->assertSame(self::BASE_URI . 'me/subscribed_apps', $this->url());
    }

    public function testSubscriptionsListsTheSubscribedFields(): void
    {
        $this->http->queue($this->json(['data' => [['subscribed_fields' => ['messages']]]]));

        $result = $this->webhooks()->subscriptions();

        $this->assertSame(['data' => [['subscribed_fields' => ['messages']]]], $result);
        $this->assertSame('GET', $this->request()->getMethod());
    }

    public function testItOmitsTheAccessTokenQueryWhenThereIsNoToken(): void
    {
        $this->http->queue($this->json([]));

        (new Webhooks($this->graphClient(null)))->subscribe();

        $this->assertSame(['subscribed_fields' => 'messages'], $this->query());
    }

    private function webhooks(): Webhooks
    {
        return new Webhooks($this->graphClient());
    }
}
