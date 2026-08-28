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

/**
 * Keeps 3.x catch blocks working now that the exceptions live in their own namespace.
 *
 * @deprecated 4.0.0 Catch Amirsarhang\Exception\InstagramException instead.
 */
if (!class_exists(Amirsarhang\InstagramException::class, false)) {
    class_alias(Amirsarhang\Exception\InstagramException::class, Amirsarhang\InstagramException::class);
}
