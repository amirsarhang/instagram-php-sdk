<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit\Resources;

use Amirsarhang\AttachmentType;
use Amirsarhang\Exception\InvalidArgumentException;
use Amirsarhang\Resources\Messages;
use Amirsarhang\Tests\TestCase;

final class MessagesTest extends TestCase
{
    public function testGetRequestsTheConversationFieldsByDefault(): void
    {
        $this->http->queue($this->json(['message' => 'Hi']));

        $result = $this->messages()->get('aWdfZAG1');

        $this->assertSame(['message' => 'Hi'], $result);
        $this->assertSame(['fields' => 'message,from,created_time,attachments'], $this->query());
    }

    public function testGetAcceptsCustomFields(): void
    {
        $this->http->queue($this->json([]));

        $this->messages()->get('aWdfZAG1', ['message']);

        $this->assertSame(['fields' => 'message'], $this->query());
    }

    public function testSendTextBuildsTheMessengerPayload(): void
    {
        $this->http->queue($this->json(['message_id' => 'aWdfZAG1']));

        $this->messages()->sendText('17841400000000000', 'Hello');

        $this->assertSame('POST', $this->request()->getMethod());
        $this->assertSame(self::BASE_URI . 'me/messages', $this->url());
        $this->assertSame([
            'recipient' => ['id' => '17841400000000000'],
            'message' => ['text' => 'Hello'],
        ], $this->requestBody());
    }

    public function testSendMediaDefaultsToAnImageAttachment(): void
    {
        $this->http->queue($this->json(['message_id' => 'aWdfZAG1']));

        $this->messages()->sendMedia('17841400000000000', 'https://example.com/photo.jpg');

        $this->assertSame([
            'recipient' => ['id' => '17841400000000000'],
            'message' => [
                'attachment' => [
                    'type' => 'image',
                    'payload' => ['url' => 'https://example.com/photo.jpg'],
                ],
            ],
        ], $this->requestBody());
    }

    public function testSendMediaAcceptsAnAttachmentTypeConstant(): void
    {
        $this->http->queue($this->json([]));

        $this->messages()->sendMedia('17841400000000000', 'https://example.com/clip.mp4', AttachmentType::VIDEO);

        $this->assertSame('video', $this->requestBody()['message']['attachment']['type']);
    }

    public function testSendMediaStillAcceptsAPlainString(): void
    {
        $this->http->queue($this->json([]));

        $this->messages()->sendMedia('17841400000000000', 'https://example.com/clip.mp4', 'video');

        $this->assertSame('video', $this->requestBody()['message']['attachment']['type']);
    }

    public function testItRejectsIncompleteArguments(): void
    {
        $messages = $this->messages();

        $this->assertRejects(
            fn () => $messages->get(''),
            'Messages::get() requires a non-empty $messageId.'
        );
        $this->assertRejects(
            fn () => $messages->sendText('', 'Hello'),
            'Messages::sendText() requires a non-empty $recipientId.'
        );
        $this->assertRejects(
            fn () => $messages->sendText('17841400000000000', ''),
            'Messages::sendText() requires a non-empty $text.'
        );
        $this->assertRejects(
            fn () => $messages->sendMedia('', 'https://example.com/a.jpg'),
            'Messages::sendMedia() requires a non-empty $recipientId.'
        );
        $this->assertRejects(
            fn () => $messages->sendMedia('17841400000000000', ''),
            'Messages::sendMedia() requires a non-empty $url.'
        );
    }

    private function messages(): Messages
    {
        return new Messages($this->graphClient());
    }
}
