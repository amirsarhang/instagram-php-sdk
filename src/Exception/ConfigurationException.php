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
 * Thrown when the SDK is missing something it needs to build a request.
 */
final class ConfigurationException extends InstagramException
{
    public static function missing(string $key): self
    {
        return new self(sprintf(
            'Missing "%s" configuration. Set it in your .env file or pass it to the Config constructor.',
            $key
        ));
    }
}
