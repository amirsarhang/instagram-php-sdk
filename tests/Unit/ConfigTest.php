<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit;

use Amirsarhang\Config;
use Amirsarhang\Exception\ConfigurationException;
use Amirsarhang\Tests\TestCase;

final class ConfigTest extends TestCase
{
    public function testItExposesTheCredentialsItWasGiven(): void
    {
        $config = $this->config();

        $this->assertSame('1234567890', $config->appId());
        $this->assertSame('app-secret', $config->appSecret());
        $this->assertSame('https://example.com/instagram/callback', $config->redirectUri());
        $this->assertSame('v21.0', $config->graphVersion());
    }

    public function testItDefaultsToTheLatestKnownGraphVersion(): void
    {
        $this->assertSame(Config::DEFAULT_GRAPH_VERSION, (new Config())->graphVersion());
    }

    public function testItBuildsTheVersionedGraphBaseUri(): void
    {
        $config = new Config(graphVersion: 'v19.0');

        $this->assertSame('https://graph.instagram.com/v19.0/', $config->graphBaseUri());
    }

    public function testItToleratesSlashesAroundTheGraphVersion(): void
    {
        $config = new Config(graphVersion: '/v19.0/');

        $this->assertSame('https://graph.instagram.com/v19.0/', $config->graphBaseUri());
    }

    public function testTheOauthCredentialsAreOptionalUntilTheyAreUsed(): void
    {
        $config = new Config();

        $this->assertSame('https://graph.instagram.com/v21.0/', $config->graphBaseUri());

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Missing "INSTAGRAM_APP_ID" configuration');

        $config->appId();
    }

    public function testItReportsEachMissingCredentialByName(): void
    {
        $config = new Config(appId: 'id');

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Missing "INSTAGRAM_APP_SECRET" configuration');

        $config->appSecret();
    }

    public function testItReadsTheEnvironment(): void
    {
        $variables = [
            'INSTAGRAM_APP_ID' => 'env-app-id',
            'INSTAGRAM_APP_SECRET' => 'env-secret',
            'INSTAGRAM_CALLBACK_URL' => 'https://env.example.com/callback',
            'INSTAGRAM_GRAPH_VERSION' => 'v20.0',
        ];

        foreach ($variables as $key => $value) {
            $_ENV[$key] = $value;
        }

        $config = Config::fromEnvironment();

        $this->assertSame('env-app-id', $config->appId());
        $this->assertSame('env-secret', $config->appSecret());
        $this->assertSame('https://env.example.com/callback', $config->redirectUri());
        $this->assertSame('v20.0', $config->graphVersion());

        foreach (array_keys($variables) as $key) {
            unset($_ENV[$key]);
        }
    }

    public function testItFallsBackToTheDefaultGraphVersionFromTheEnvironment(): void
    {
        $this->forgetEnvironment('INSTAGRAM_GRAPH_VERSION');

        $this->assertSame(Config::DEFAULT_GRAPH_VERSION, Config::fromEnvironment()->graphVersion());
    }
}
