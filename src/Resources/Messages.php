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

namespace Amirsarhang\Resources;

use Amirsarhang\AttachmentType;
use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\InvalidArgumentException;

/**
 * Instagram direct messages.
 *
 * @link https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/messaging-api
 */
final class Messages extends AbstractResource
{
    public const DEFAULT_FIELDS = ['message', 'from', 'created_time', 'attachments'];

    /**
     * @param array<int, string> $fields
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function get(string $messageId, array $fields = self::DEFAULT_FIELDS): array
    {
        if ($messageId === '') {
            throw InvalidArgumentException::empty('messageId', __METHOD__);
        }

        return $this->client->get($messageId, $this->fieldsQuery($fields));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function sendText(string $recipientId, string $text): array
    {
        if ($recipientId === '') {
            throw InvalidArgumentException::empty('recipientId', __METHOD__);
        }

        if ($text === '') {
            throw InvalidArgumentException::empty('text', __METHOD__);
        }

        return $this->send($recipientId, ['text' => $text]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function sendMedia(
        string $recipientId,
        string $url,
        string $type = AttachmentType::IMAGE
    ): array {
        if ($recipientId === '') {
            throw InvalidArgumentException::empty('recipientId', __METHOD__);
        }

        if ($url === '') {
            throw InvalidArgumentException::empty('url', __METHOD__);
        }

        return $this->send($recipientId, [
            'attachment' => [
                'type' => $type,
                'payload' => ['url' => $url],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $message
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    private function send(string $recipientId, array $message): array
    {
        return $this->client->post('/me/messages', [
            'recipient' => ['id' => $recipientId],
            'message' => $message,
        ]);
    }
}
