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

/**
 * The Instagram account the access token belongs to.
 *
 * @link https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login
 */
final class Account extends AbstractResource
{
    public const DEFAULT_FIELDS = [
        'user_id',
        'name',
        'biography',
        'username',
        'followers_count',
        'follows_count',
        'profile_picture_url',
        'website',
    ];

    /**
     * @param array<int, string> $fields
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    public function me(array $fields = self::DEFAULT_FIELDS): array
    {
        return $this->client->get('/me', $this->fieldsQuery($fields));
    }
}
