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

use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\InvalidArgumentException;

/**
 * Comments on your media, and replies to them.
 *
 * @link https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/content-publishing
 */
final class Comments extends AbstractResource
{
    public const DEFAULT_FIELDS = ['timestamp', 'text'];

    /**
     * @param array<int, string> $fields
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function get(string $commentId, array $fields = self::DEFAULT_FIELDS): array
    {
        if ($commentId === '') {
            throw InvalidArgumentException::empty('commentId', __METHOD__);
        }

        return $this->client->get($commentId, $this->fieldsQuery($fields));
    }

    /**
     * Reply to a comment, or comment on a media object.
     *
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function reply(string $commentId, string $message): array
    {
        if ($commentId === '') {
            throw InvalidArgumentException::empty('commentId', __METHOD__);
        }

        if ($message === '') {
            throw InvalidArgumentException::empty('message', __METHOD__);
        }

        return $this->client->post($commentId . '/replies', ['message' => $message]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function hide(string $commentId, bool $hidden = true): array
    {
        if ($commentId === '') {
            throw InvalidArgumentException::empty('commentId', __METHOD__);
        }

        return $this->client->post($commentId, ['hide' => $hidden]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function delete(string $commentId): array
    {
        if ($commentId === '') {
            throw InvalidArgumentException::empty('commentId', __METHOD__);
        }

        return $this->client->delete($commentId);
    }
}
