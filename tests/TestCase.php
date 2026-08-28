<?php

declare(strict_types=1);

namespace Amirsarhang\Tests;

use Amirsarhang\Config;
use Amirsarhang\Environment;
use Amirsarhang\Exception\InvalidArgumentException;
use Amirsarhang\Http\GraphClient;
use Amirsarhang\Tests\Double\MockHttpClient;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\RequestInterface;

abstract class TestCase extends BaseTestCase
{
    protected const BASE_URI = 'https://graph.instagram.com/v21.0/';

    protected const TOKEN = 'token-123';

    protected MockHttpClient $http;

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = new MockHttpClient();
    }

    protected function tearDown(): void
    {
        Environment::reset();

        parent::tearDown();
    }

    protected function config(): Config
    {
        return new Config(
            appId: '1234567890',
            appSecret: 'app-secret',
            redirectUri: 'https://example.com/instagram/callback',
            graphVersion: 'v21.0'
        );
    }

    protected function graphClient(?string $token = self::TOKEN): GraphClient
    {
        return new GraphClient(self::BASE_URI, $token, $this->http);
    }

    /**
     * @param array<string, mixed> $body
     */
    protected function json(array $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], (string) json_encode($body));
    }

    protected function request(int $index = 0): RequestInterface
    {
        $requests = $this->http->requests();

        $this->assertArrayHasKey($index, $requests, "No request was sent at index $index.");

        return $requests[$index];
    }

    /**
     * @return array<string, mixed>
     */
    protected function requestBody(int $index = 0): array
    {
        return (array) json_decode((string) $this->request($index)->getBody(), true);
    }

    /**
     * @return array<string, string>
     */
    protected function formBody(int $index = 0): array
    {
        parse_str((string) $this->request($index)->getBody(), $body);

        /** @var array<string, string> $body */
        return $body;
    }

    /**
     * @return array<string, string>
     */
    protected function query(int $index = 0): array
    {
        parse_str($this->request($index)->getUri()->getQuery(), $query);

        /** @var array<string, string> $query */
        return $query;
    }

    protected function url(int $index = 0): string
    {
        return (string) $this->request($index)->getUri()->withQuery('');
    }

    /**
     * Assert that a call is rejected before it reaches the network.
     *
     * @param callable(): mixed $call
     */
    protected function assertRejects(callable $call, string $message): void
    {
        try {
            $call();
        } catch (InvalidArgumentException $e) {
            $this->assertSame($message, $e->getMessage());

            return;
        }

        $this->fail('Expected an InvalidArgumentException: ' . $message);
    }

    /**
     * Hide a variable from Environment, including any real .env on the machine.
     */
    protected function forgetEnvironment(string $key): void
    {
        Environment::load();

        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);
    }
}
