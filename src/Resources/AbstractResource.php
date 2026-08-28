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

namespace Amirsarhang\Resources;

use Amirsarhang\Http\GraphClient;

/**
 * Shared plumbing for the endpoint groups.
 */
abstract class AbstractResource
{
    public function __construct(protected GraphClient $client)
    {
    }

    /**
     * @param array<int, string> $fields
     * @return array<string, string>
     */
    protected function fieldsQuery(array $fields): array
    {
        return $fields === [] ? [] : ['fields' => implode(',', $fields)];
    }
}
