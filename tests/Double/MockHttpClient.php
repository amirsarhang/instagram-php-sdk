<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Double;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A PSR-18 client that replays queued responses and records what it was asked to send.
 */
final class MockHttpClient implements ClientInterface
{
    /**
     * @var list<ResponseInterface|ClientExceptionInterface>
     */
    private array $queue = [];

    /**
     * @var list<RequestInterface>
     */
    private array $requests = [];

    public function queue(ResponseInterface|ClientExceptionInterface ...$responses): void
    {
        foreach ($responses as $response) {
            $this->queue[] = $response;
        }
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        $next = array_shift($this->queue);

        if ($next === null) {
            throw new \LogicException(sprintf(
                'No response queued for %s %s.',
                $request->getMethod(),
                $request->getUri()
            ));
        }

        if ($next instanceof ClientExceptionInterface) {
            throw $next;
        }

        return $next;
    }

    /**
     * @return list<RequestInterface>
     */
    public function requests(): array
    {
        return $this->requests;
    }

    public function count(): int
    {
        return count($this->requests);
    }
}
