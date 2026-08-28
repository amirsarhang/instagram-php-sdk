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

namespace Amirsarhang;

/**
 * The attachment types Instagram accepts on a direct message.
 *
 * @link https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/messaging-api
 */
final class AttachmentType
{
    public const IMAGE = 'image';

    public const VIDEO = 'video';

    public const AUDIO = 'audio';
}
