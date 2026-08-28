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

use Amirsarhang\Exception\ConfigurationException;

/**
 * The credentials and Graph API version the SDK talks to Instagram with.
 */
final class Config
{
    public const GRAPH_HOST = 'https://graph.instagram.com';

    public const DEFAULT_GRAPH_VERSION = 'v21.0';

    /**
     * The OAuth credentials are optional; only the calls that need them will complain.
     */
    public function __construct(
        private ?string $appId = null,
        private ?string $appSecret = null,
        private ?string $redirectUri = null,
        private string $graphVersion = self::DEFAULT_GRAPH_VERSION
    ) {
    }

    /**
     * Read the configuration from the environment, and from a .env file when
     * vlucas/phpdotenv is installed.
     */
    public static function fromEnvironment(): self
    {
        return new self(
            Environment::get('INSTAGRAM_APP_ID'),
            Environment::get('INSTAGRAM_APP_SECRET'),
            Environment::get('INSTAGRAM_CALLBACK_URL'),
            Environment::get('INSTAGRAM_GRAPH_VERSION') ?? self::DEFAULT_GRAPH_VERSION
        );
    }

    /**
     * @throws ConfigurationException
     */
    public function appId(): string
    {
        return $this->appId ?? throw ConfigurationException::missing('INSTAGRAM_APP_ID');
    }

    /**
     * @throws ConfigurationException
     */
    public function appSecret(): string
    {
        return $this->appSecret ?? throw ConfigurationException::missing('INSTAGRAM_APP_SECRET');
    }

    /**
     * @throws ConfigurationException
     */
    public function redirectUri(): string
    {
        return $this->redirectUri ?? throw ConfigurationException::missing('INSTAGRAM_CALLBACK_URL');
    }

    public function graphVersion(): string
    {
        return $this->graphVersion;
    }

    /**
     * The Graph API root every endpoint is resolved against.
     */
    public function graphBaseUri(): string
    {
        return self::GRAPH_HOST . '/' . trim($this->graphVersion, '/') . '/';
    }
}
