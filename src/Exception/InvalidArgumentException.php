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

/**
 * Thrown before a request is sent, when an argument cannot produce a valid call.
 */
final class InvalidArgumentException extends InstagramException
{
    /**
     * @param string $method Pass __METHOD__; the namespace is trimmed off for readability.
     */
    public static function empty(string $argument, string $method): self
    {
        $short = str_contains($method, '\\')
            ? substr($method, (int) strrpos($method, '\\') + 1)
            : $method;

        return new self(sprintf('%s() requires a non-empty $%s.', $short, $argument));
    }
}
