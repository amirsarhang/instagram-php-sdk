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

namespace Amirsarhang\Http;

use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\TransportException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Sends the requests and turns every response into an array.
 */
final class GraphClient
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    /**
     * @param string $baseUri The root that relative endpoints are resolved against.
     * @param string|null $token Sent as the bearer token on every request.
     */
    public function __construct(
        private string $baseUri,
        private ?string $token = null,
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->client = $client ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * Return a copy that authenticates with a different token.
     */
    public function withToken(?string $token): self
    {
        return new self(
            $this->baseUri,
            $token,
            $this->client,
            $this->requestFactory,
            $this->streamFactory
        );
    }

    public function token(): ?string
    {
        return $this->token;
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     *
     * @throws GraphException|TransportException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->send($this->request('GET', $endpoint, $query));
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     *
     * @throws GraphException|TransportException
     */
    public function post(string $endpoint, array $body = [], array $query = []): array
    {
        return $this->send($this->request('POST', $endpoint, $query, $body));
    }

    /**
     * Instagram expects the OAuth code exchange as a form post, not as JSON.
     *
     * @param array<string, scalar|null> $fields
     * @return array<string, mixed>
     *
     * @throws GraphException|TransportException
     */
    public function postForm(string $endpoint, array $fields): array
    {
        $request = $this->request('POST', $endpoint)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query($fields)));

        return $this->send($request);
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     *
     * @throws GraphException|TransportException
     */
    public function delete(string $endpoint, array $body = [], array $query = []): array
    {
        return $this->send($this->request('DELETE', $endpoint, $query, $body));
    }

    /**
     * @param array<string, scalar|null> $query
     * @param array<string, mixed>|null $body
     */
    private function request(string $method, string $endpoint, array $query = [], ?array $body = null): RequestInterface
    {
        $request = $this->requestFactory
            ->createRequest($method, $this->uri($endpoint, $query))
            ->withHeader('Accept', 'application/json');

        if ($this->token !== null && $this->token !== '') {
            $request = $request->withHeader('Authorization', 'Bearer ' . $this->token);
        }

        if ($body !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream(
                    (string) json_encode($body, JSON_THROW_ON_ERROR)
                ));
        }

        return $request;
    }

    /**
     * Resolve the endpoint against the base URI and merge in the extra query parameters.
     *
     * @param array<string, scalar|null> $query
     */
    private function uri(string $endpoint, array $query): string
    {
        [$path, $existingQuery] = array_pad(explode('?', $endpoint, 2), 2, '');

        $uri = str_contains($path, '://') ? $path : $this->baseUri . ltrim($path, '/');

        parse_str($existingQuery, $parameters);

        $parameters = array_filter(
            array_merge($parameters, $query),
            static fn ($value): bool => $value !== null
        );

        return $parameters === [] ? $uri : $uri . '?' . http_build_query($parameters);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GraphException|TransportException
     */
    private function send(RequestInterface $request): array
    {
        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw TransportException::forUri($request->getMethod(), (string) $request->getUri(), $e);
        }

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GraphException
     */
    private function decode(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        if ($response->getStatusCode() >= 400) {
            throw GraphException::fromResponse($response, is_array($decoded) ? $decoded : null, $body);
        }

        if (!is_array($decoded)) {
            throw GraphException::malformedResponse($response, $body);
        }

        return $decoded;
    }
}
