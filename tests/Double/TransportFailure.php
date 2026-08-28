<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Double;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Stands in for whatever a PSR-18 client throws when it cannot reach the host.
 */
final class TransportFailure extends \RuntimeException implements ClientExceptionInterface
{
}
