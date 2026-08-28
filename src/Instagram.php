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

namespace Amirsarhang;

use Amirsarhang\Auth\OAuth;
use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\InvalidArgumentException;
use Amirsarhang\Http\GraphClient;
use Amirsarhang\Resources\Account;
use Amirsarhang\Resources\Comments;
use Amirsarhang\Resources\Messages;
use Amirsarhang\Resources\Webhooks;
use Psr\Http\Client\ClientInterface;

/**
 * It's an unofficial Instagram PHP SDK.
 */
class Instagram
{
    private Config $config;

    private GraphClient $client;

    /**
     * @param string|null $token The Instagram AccessToken.
     * @param Config|null $config Defaults to the values found in the environment.
     * @param ClientInterface|null $http Any PSR-18 client; discovered when not given.
     */
    public function __construct(
        ?string $token = null,
        ?Config $config = null,
        ?ClientInterface $http = null
    ) {
        $this->config = $config ?? Config::fromEnvironment();
        $this->client = new GraphClient($this->config->graphBaseUri(), $token, $http);
    }

    public function comments(): Comments
    {
        return new Comments($this->client);
    }

    public function messages(): Messages
    {
        return new Messages($this->client);
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks($this->client);
    }

    public function account(): Account
    {
        return new Account($this->client);
    }

    public function oauth(): OAuth
    {
        return new OAuth($this->config, $this->client);
    }

    /**
     * The transport, for endpoints this SDK does not wrap yet.
     */
    public function graph(): GraphClient
    {
        return $this->client;
    }

    public function config(): Config
    {
        return $this->config;
    }

    /**
     * Return a copy authenticated with a different access token.
     */
    public function withToken(?string $token): static
    {
        $clone = clone $this;
        $clone->client = $this->client->withToken($token);

        return $clone;
    }

    /**
     * GET request on Instagram Graph API.
     *
     * @param string $endpoint Destination Instagram endpoint that request should be sent to there.
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->client->get($endpoint, $query);
    }

    /**
     * POST request on Instagram Graph API.
     *
     * @param string $endpoint Destination Instagram endpoint that request should be sent to there.
     * @param array<string, mixed> $params Post parameters.
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    public function post(string $endpoint, array $params = [], array $query = []): array
    {
        return $this->client->post($endpoint, $params, $query);
    }

    /**
     * DELETE request on Instagram Graph API.
     *
     * @param string $endpoint Destination Instagram endpoint that request should be sent to there.
     * @param array<string, mixed> $params DELETE parameters.
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    public function delete(string $endpoint, array $params = [], array $query = []): array
    {
        return $this->client->delete($endpoint, $params, $query);
    }

    /**
     * @param array<int, string> $permissions Instagram permissions
     *
     * @deprecated 4.0.0 Use oauth()->loginUrl() instead.
     *
     * @throws InvalidArgumentException
     */
    public function getLoginUrl(array $permissions): string
    {
        return $this->oauth()->loginUrl($permissions);
    }

    /**
     * @param string $code The code that returned by Instagram after user callback.
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use oauth()->connect() instead. This no longer returns false on failure.
     *
     * @throws GraphException
     */
    public function getPageAccessToken(string $code): array
    {
        return $this->oauth()->connect($code);
    }

    /**
     * @param array<int, string> $subscribed_fields Page field (example: ["messages"])
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use webhooks()->subscribe() instead. The token now comes from the constructor.
     *
     * @throws GraphException
     */
    public function subscribeWebhook(string $accessToken, array $subscribed_fields = ['messages']): array
    {
        return $this->withToken($accessToken)->webhooks()->subscribe($subscribed_fields);
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use withToken($access_token)->account()->me() instead.
     *
     * @throws GraphException
     */
    public function getConnectedAccount(string $access_token): array
    {
        return $this->withToken($access_token)->account()->me();
    }

    /**
     * @param array<int, string> $fields Required fields
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use comments()->get() instead.
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function getComment(string $comment_id, array $fields = Comments::DEFAULT_FIELDS): array
    {
        return $this->comments()->get($comment_id, $fields);
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use comments()->reply() instead.
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function addComment(string $recipient_id, string $message): array
    {
        return $this->comments()->reply($recipient_id, $message);
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use comments()->delete() instead.
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function deleteComment(string $comment_id): array
    {
        return $this->comments()->delete($comment_id);
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use comments()->hide() instead.
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function hideComment(string $comment_id, bool $status): array
    {
        return $this->comments()->hide($comment_id, $status);
    }

    /**
     * @param array<int, string> $fields Required fields
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use messages()->get() instead.
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function getMessage(string $message_id, array $fields = []): array
    {
        return $this->messages()->get($message_id, $fields === [] ? Messages::DEFAULT_FIELDS : $fields);
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use messages()->sendText() instead.
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function addTextMessage(string $recipient_id, string $message): array
    {
        return $this->messages()->sendText($recipient_id, $message);
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated 4.0.0 Use messages()->sendMedia() instead.
     *
     * @throws GraphException|InvalidArgumentException
     */
    public function addMediaMessage(string $recipient_id, string $url, string $type = 'image'): array
    {
        return $this->messages()->sendMedia($recipient_id, $url, $type);
    }
}
