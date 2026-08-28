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

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Thrown when the request never produced a usable response.
 */
final class TransportException extends InstagramException
{
    public static function forUri(string $method, string $uri, ClientExceptionInterface $previous): self
    {
        return new self(
            sprintf('Could not reach Instagram (%s %s): %s', $method, $uri, $previous->getMessage()),
            0,
            $previous
        );
    }
}
