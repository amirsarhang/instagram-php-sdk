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

namespace Amirsarhang\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when Instagram answers with an error, or with a body that is not JSON.
 *
 * @link https://developers.facebook.com/docs/graph-api/guides/error-handling
 */
final class GraphException extends InstagramException
{
    /**
     * @param array<string, mixed> $error The "error" object Instagram returned.
     */
    private function __construct(
        string $message,
        private int $statusCode,
        private array $error = [],
        private string $body = ''
    ) {
        parent::__construct($message, $this->errorCode() ?? $statusCode);
    }

    /**
     * @param array<string, mixed>|null $decoded
     */
    public static function fromResponse(ResponseInterface $response, ?array $decoded, string $body): self
    {
        $error = is_array($decoded['error'] ?? null) ? $decoded['error'] : [];
        $status = $response->getStatusCode();

        $message = is_string($error['message'] ?? null)
            ? $error['message']
            : sprintf('Instagram returned HTTP %d.', $status);

        return new self($message, $status, $error, $body);
    }

    /**
     * Instagram answered successfully, but without the token the flow needs.
     */
    public static function missingAccessToken(): self
    {
        return new self('Instagram did not return an access token.', 0);
    }

    public static function malformedResponse(ResponseInterface $response, string $body): self
    {
        return new self(
            sprintf('Instagram returned a body that is not JSON: %s', self::truncate($body)),
            $response->getStatusCode(),
            [],
            $body
        );
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * The Graph error type, for example "OAuthException".
     */
    public function errorType(): ?string
    {
        return is_string($this->error['type'] ?? null) ? $this->error['type'] : null;
    }

    /**
     * The Graph error code, for example 190 for an expired token.
     */
    public function errorCode(): ?int
    {
        return is_int($this->error['code'] ?? null) ? $this->error['code'] : null;
    }

    public function errorSubcode(): ?int
    {
        return is_int($this->error['error_subcode'] ?? null) ? $this->error['error_subcode'] : null;
    }

    /**
     * The identifier to quote when reporting a problem to Meta.
     */
    public function traceId(): ?string
    {
        return is_string($this->error['fbtrace_id'] ?? null) ? $this->error['fbtrace_id'] : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function error(): array
    {
        return $this->error;
    }

    public function body(): string
    {
        return $this->body;
    }

    private static function truncate(string $body): string
    {
        return strlen($body) > 500 ? substr($body, 0, 500) . '...' : $body;
    }
}
