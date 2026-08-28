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

use Dotenv\Dotenv;

/**
 * Reads configuration from the environment.
 *
 * @internal
 */
final class Environment
{
    private static bool $loaded = false;

    /**
     * Returns null when the variable is absent or empty.
     */
    public static function get(string $key): ?string
    {
        self::load();

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * Load the .env file once per process, if phpdotenv is installed.
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        self::$loaded = true;

        if (class_exists(Dotenv::class)) {
            Dotenv::createImmutable(self::projectRoot())->safeLoad();
        }
    }

    /**
     * Forget the loaded state so the next read re-reads the .env file.
     */
    public static function reset(): void
    {
        self::$loaded = false;
    }

    /**
     * Walk up from src/ to the first directory holding a .env file.
     */
    private static function projectRoot(): string
    {
        $directory = __DIR__;

        while (($parent = dirname($directory)) !== $directory) {
            $directory = $parent;

            if (is_file($directory . '/.env')) {
                return $directory;
            }
        }

        return dirname(__DIR__);
    }
}
