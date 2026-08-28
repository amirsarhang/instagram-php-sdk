<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit;

use Amirsarhang\Exception\InstagramException;
use Amirsarhang\Instagram;
use Amirsarhang\Tests\TestCase;

/**
 * The 3.x method names kept on the client, so existing call sites keep working.
 */
final class DeprecatedApiTest extends TestCase
{
    public function testGetLoginUrlForwardsToOauth(): void
    {
        $url = $this->instagram()->getLoginUrl(['instagram_business_basic']);

        $this->assertStringStartsWith('https://www.instagram.com/oauth/authorize?', $url);
        $this->assertStringContainsString('client_id=1234567890', $url);
    }

    public function testGetPageAccessTokenForwardsToOauthConnect(): void
    {
        $this->http->queue(
            $this->json(['access_token' => 'short-lived']),
            $this->json(['access_token' => 'long-lived']),
            $this->json(['id' => '1234567890123456', 'username' => 'test_page'])
        );

        $result = $this->instagram()->getPageAccessToken('callback-code');

        $this->assertSame([
            'access_token' => 'long-lived',
            'id' => '1234567890123456',
            'username' => 'test_page',
        ], $result);
    }

    public function testSubscribeWebhookUsesTheTokenItIsGiven(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->instagram()->subscribeWebhook('page-token', ['messages', 'comments']);

        $this->assertSame(self::BASE_URI . 'me/subscribed_apps', $this->url());
        $this->assertSame([
            'subscribed_fields' => 'messages,comments',
            'access_token' => 'page-token',
        ], $this->query());
        $this->assertSame('Bearer page-token', $this->request()->getHeaderLine('Authorization'));
    }

    public function testGetConnectedAccountForwardsToTheAccountResource(): void
    {
        $this->http->queue($this->json(['username' => 'test_page']));

        $result = $this->instagram()->getConnectedAccount('page-token');

        $this->assertSame(['username' => 'test_page'], $result);
        $this->assertSame(self::BASE_URI . 'me', $this->url());
        $this->assertSame('Bearer page-token', $this->request()->getHeaderLine('Authorization'));
    }

    public function testGetCommentForwardsToTheCommentsResource(): void
    {
        $this->http->queue($this->json(['text' => 'Nice!']));

        $result = $this->instagram()->getComment('17900000000000000');

        $this->assertSame(['text' => 'Nice!'], $result);
        $this->assertSame(['fields' => 'timestamp,text'], $this->query());
    }

    public function testAddCommentForwardsToTheCommentsResource(): void
    {
        $this->http->queue($this->json(['id' => '1']));

        $this->instagram()->addComment('17900000000000000', 'Thanks!');

        $this->assertSame(self::BASE_URI . '17900000000000000/replies', $this->url());
        $this->assertSame(['message' => 'Thanks!'], $this->requestBody());
    }

    public function testDeleteCommentForwardsToTheCommentsResource(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->instagram()->deleteComment('17900000000000000');

        $this->assertSame('DELETE', $this->request()->getMethod());
    }

    public function testHideCommentForwardsToTheCommentsResource(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->instagram()->hideComment('17900000000000000', true);

        $this->assertSame(['hide' => true], $this->requestBody());
    }

    public function testGetMessageKeepsTheThreeXDefaultFields(): void
    {
        $this->http->queue($this->json(['message' => 'Hi']));

        $this->instagram()->getMessage('aWdfZAG1');

        $this->assertSame(['fields' => 'message,from,created_time,attachments'], $this->query());
    }

    public function testAddTextMessageForwardsToTheMessagesResource(): void
    {
        $this->http->queue($this->json(['message_id' => 'aWdfZAG1']));

        $this->instagram()->addTextMessage('17841400000000000', 'Hello');

        $this->assertSame([
            'recipient' => ['id' => '17841400000000000'],
            'message' => ['text' => 'Hello'],
        ], $this->requestBody());
    }

    public function testAddMediaMessageForwardsToTheMessagesResource(): void
    {
        $this->http->queue($this->json([]));

        $this->instagram()->addMediaMessage('17841400000000000', 'https://example.com/clip.mp4', 'video');

        $this->assertSame('video', $this->requestBody()['message']['attachment']['type']);
    }

    public function testTheThreeXExceptionNameStillCatchesEverything(): void
    {
        $this->http->queue($this->json(['error' => ['message' => 'Nope']], 400));

        $this->expectException(\Amirsarhang\InstagramException::class);

        $this->instagram()->get('/me');
    }

    public function testTheThreeXExceptionNameIsTheNamespacedOne(): void
    {
        $this->assertTrue(class_exists(\Amirsarhang\InstagramException::class));
        $this->assertSame(
            InstagramException::class,
            (new \ReflectionClass(\Amirsarhang\InstagramException::class))->getName()
        );
    }

    private function instagram(): Instagram
    {
        return new Instagram(self::TOKEN, $this->config(), $this->http);
    }
}
