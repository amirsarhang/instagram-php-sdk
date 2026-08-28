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
 * The webhook fields your app is subscribed to for the connected account.
 *
 * @link https://developers.facebook.com/docs/graph-api/webhooks/reference/instagram
 */
final class Webhooks extends AbstractResource
{
    public const DEFAULT_FIELDS = ['messages'];

    /**
     * Your app only receives notifications for fields you have also configured
     * in the App Dashboard.
     *
     * @param array<int, string> $fields
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    public function subscribe(array $fields = self::DEFAULT_FIELDS): array
    {
        return $this->client->post('/me/subscribed_apps', [], [
            'subscribed_fields' => implode(',', $fields),
            'access_token' => $this->client->token(),
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    public function unsubscribe(): array
    {
        return $this->client->delete('/me/subscribed_apps', [], [
            'access_token' => $this->client->token(),
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    public function subscriptions(): array
    {
        return $this->client->get('/me/subscribed_apps', [
            'access_token' => $this->client->token(),
        ]);
    }
}
