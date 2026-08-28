<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit\Http;

use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\TransportException;
use Amirsarhang\Tests\Double\TransportFailure;
use Amirsarhang\Tests\TestCase;
use Nyholm\Psr7\Response;

final class GraphClientTest extends TestCase
{
    public function testItResolvesRelativeEndpointsAgainstTheBaseUri(): void
    {
        $this->http->queue($this->json([]), $this->json([]));

        $this->graphClient()->get('/me');
        $this->graphClient()->get('me');

        $this->assertSame(self::BASE_URI . 'me', $this->url(0));
        $this->assertSame(self::BASE_URI . 'me', $this->url(1));
    }

    public function testItLeavesAbsoluteEndpointsAlone(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->get('https://api.instagram.com/oauth/access_token');

        $this->assertSame('https://api.instagram.com/oauth/access_token', $this->url());
    }

    public function testItMergesTheQueryArrayIntoTheEndpointQueryString(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->get('/me?fields=id', ['access_token' => 'abc']);

        $this->assertSame(['fields' => 'id', 'access_token' => 'abc'], $this->query());
    }

    public function testTheQueryArrayWinsOverTheEndpointQueryString(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->get('/me?fields=id', ['fields' => 'username']);

        $this->assertSame(['fields' => 'username'], $this->query());
    }

    public function testItDropsNullQueryParameters(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->get('/me', ['fields' => 'id', 'access_token' => null]);

        $this->assertSame(['fields' => 'id'], $this->query());
    }

    public function testItSendsTheTokenAsABearerHeader(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->get('/me');

        $this->assertSame('Bearer ' . self::TOKEN, $this->request()->getHeaderLine('Authorization'));
        $this->assertSame('application/json', $this->request()->getHeaderLine('Accept'));
    }

    public function testItSendsNoAuthorizationHeaderWithoutAToken(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient(null)->get('/me');

        $this->assertFalse($this->request()->hasHeader('Authorization'));
    }

    public function testGetSendsNoBody(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->get('/me');

        $this->assertSame('', (string) $this->request()->getBody());
        $this->assertFalse($this->request()->hasHeader('Content-Type'));
    }

    public function testPostSendsAJsonBody(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->post('/me/messages', ['message' => 'Hello']);

        $this->assertSame('POST', $this->request()->getMethod());
        $this->assertSame('application/json', $this->request()->getHeaderLine('Content-Type'));
        $this->assertSame(['message' => 'Hello'], $this->requestBody());
    }

    public function testDeleteSendsAJsonBody(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->delete('/17900000000000000', ['hide' => true]);

        $this->assertSame('DELETE', $this->request()->getMethod());
        $this->assertSame(['hide' => true], $this->requestBody());
    }

    public function testPostFormSendsUrlEncodedFields(): void
    {
        $this->http->queue($this->json([]));

        $this->graphClient()->postForm('https://api.instagram.com/oauth/access_token', [
            'code' => 'callback-code',
            'grant_type' => 'authorization_code',
        ]);

        $this->assertSame(
            'application/x-www-form-urlencoded',
            $this->request()->getHeaderLine('Content-Type')
        );
        $this->assertSame([
            'code' => 'callback-code',
            'grant_type' => 'authorization_code',
        ], $this->formBody());
    }

    public function testItReturnsTheDecodedBody(): void
    {
        $this->http->queue($this->json(['data' => [['id' => '1']]]));

        $this->assertSame(['data' => [['id' => '1']]], $this->graphClient()->get('/me/media'));
    }

    public function testWithTokenReturnsACopyUsingTheNewToken(): void
    {
        $this->http->queue($this->json([]));

        $client = $this->graphClient();
        $other = $client->withToken('another-token');

        $other->get('/me');

        $this->assertNotSame($client, $other);
        $this->assertSame(self::TOKEN, $client->token());
        $this->assertSame('another-token', $other->token());
        $this->assertSame('Bearer another-token', $this->request()->getHeaderLine('Authorization'));
    }

    public function testItThrowsWhenInstagramReportsAnError(): void
    {
        $this->http->queue($this->json([
            'error' => [
                'message' => 'Invalid OAuth access token.',
                'type' => 'OAuthException',
                'code' => 190,
            ],
        ], 401));

        $this->expectException(GraphException::class);
        $this->expectExceptionMessage('Invalid OAuth access token.');

        $this->graphClient()->get('/me');
    }

    public function testTheErrorEnvelopeIsReadable(): void
    {
        $this->http->queue($this->json([
            'error' => [
                'message' => 'Invalid OAuth access token.',
                'type' => 'OAuthException',
                'code' => 190,
                'error_subcode' => 463,
                'fbtrace_id' => 'AbCdEfG',
            ],
        ], 401));

        try {
            $this->graphClient()->get('/me');
            $this->fail('Expected a GraphException.');
        } catch (GraphException $e) {
            $this->assertSame(401, $e->statusCode());
            $this->assertSame('OAuthException', $e->errorType());
            $this->assertSame(190, $e->errorCode());
            $this->assertSame(463, $e->errorSubcode());
            $this->assertSame('AbCdEfG', $e->traceId());
            $this->assertSame(190, $e->getCode());
            $this->assertSame([
                'message' => 'Invalid OAuth access token.',
                'type' => 'OAuthException',
                'code' => 190,
                'error_subcode' => 463,
                'fbtrace_id' => 'AbCdEfG',
            ], $e->error());
        }
    }

    public function testItFallsBackToTheStatusCodeWhenThereIsNoErrorEnvelope(): void
    {
        $this->http->queue(new Response(500, [], 'null'));

        try {
            $this->graphClient()->get('/me');
            $this->fail('Expected a GraphException.');
        } catch (GraphException $e) {
            $this->assertSame('Instagram returned HTTP 500.', $e->getMessage());
            $this->assertSame(500, $e->statusCode());
            $this->assertNull($e->errorType());
        }
    }

    public function testItThrowsWhenTheBodyIsNotJson(): void
    {
        $this->http->queue(new Response(200, [], '<html>Bad Gateway</html>'));

        $this->expectException(GraphException::class);
        $this->expectExceptionMessage('Instagram returned a body that is not JSON: <html>Bad Gateway</html>');

        $this->graphClient()->get('/me');
    }

    public function testItThrowsATransportExceptionWhenTheHostIsUnreachable(): void
    {
        $failure = new TransportFailure('Connection timed out');
        $this->http->queue($failure);

        try {
            $this->graphClient()->get('/me');
            $this->fail('Expected a TransportException.');
        } catch (TransportException $e) {
            $this->assertStringContainsString('Could not reach Instagram', $e->getMessage());
            $this->assertStringContainsString('GET ' . self::BASE_URI . 'me', $e->getMessage());
            $this->assertSame($failure, $e->getPrevious());
        }
    }
}
