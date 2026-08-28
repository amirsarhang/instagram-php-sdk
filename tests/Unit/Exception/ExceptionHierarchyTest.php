<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit\Exception;

use Amirsarhang\Exception\ConfigurationException;
use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\InstagramException;
use Amirsarhang\Exception\InvalidArgumentException;
use Amirsarhang\Exception\TransportException;
use Amirsarhang\Tests\Double\TransportFailure;
use Nyholm\Psr7\Response;
use Amirsarhang\Tests\TestCase;

final class ExceptionHierarchyTest extends TestCase
{
    public function testEveryExceptionIsCatchableAsAnInstagramException(): void
    {
        $classes = [
            ConfigurationException::class,
            InvalidArgumentException::class,
            GraphException::class,
            TransportException::class,
        ];

        foreach ($classes as $class) {
            $this->assertTrue(
                is_subclass_of($class, InstagramException::class),
                $class . ' must be catchable as an InstagramException.'
            );
        }
    }

    public function testTheBaseExceptionIsARuntimeException(): void
    {
        $this->assertInstanceOf(\RuntimeException::class, new InstagramException('Boom'));
    }

    public function testTransportExceptionNamesTheRequestItFailedOn(): void
    {
        $previous = new TransportFailure('Connection refused');

        $exception = TransportException::forUri('GET', 'https://graph.instagram.com/v21.0/me', $previous);

        $this->assertSame(
            'Could not reach Instagram (GET https://graph.instagram.com/v21.0/me): Connection refused',
            $exception->getMessage()
        );
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConfigurationExceptionNamesTheMissingKey(): void
    {
        $this->assertStringContainsString(
            'Missing "INSTAGRAM_APP_ID" configuration',
            ConfigurationException::missing('INSTAGRAM_APP_ID')->getMessage()
        );
    }

    public function testGraphExceptionTruncatesAVeryLongBody(): void
    {
        $body = str_repeat('x', 900);
        $this->http->queue(new Response(200, [], $body));

        try {
            $this->graphClient()->get('/me');
            $this->fail('Expected a GraphException.');
        } catch (GraphException $e) {
            $this->assertStringEndsWith('...', $e->getMessage());
            $this->assertLessThan(600, strlen($e->getMessage()));
            $this->assertSame($body, $e->body());
        }
    }
}
