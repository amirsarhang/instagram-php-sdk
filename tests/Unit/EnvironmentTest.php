<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit;

use Amirsarhang\Environment;
use Amirsarhang\Tests\TestCase;

final class EnvironmentTest extends TestCase
{
    public function testItReadsFromEnvSuperGlobal(): void
    {
        $_ENV['INSTAGRAM_APP_ID'] = 'from-env';

        $this->assertSame('from-env', Environment::get('INSTAGRAM_APP_ID'));

        unset($_ENV['INSTAGRAM_APP_ID']);
    }

    public function testItFallsBackToServerSuperGlobal(): void
    {
        $this->forgetEnvironment('INSTAGRAM_APP_ID');
        $_SERVER['INSTAGRAM_APP_ID'] = 'from-server';

        $this->assertSame('from-server', Environment::get('INSTAGRAM_APP_ID'));

        unset($_SERVER['INSTAGRAM_APP_ID']);
    }

    public function testItReturnsNullWhenTheVariableIsMissing(): void
    {
        $this->forgetEnvironment('INSTAGRAM_APP_SECRET');

        $this->assertNull(Environment::get('INSTAGRAM_APP_SECRET'));
    }

    public function testItTreatsAnEmptyValueAsMissing(): void
    {
        $_ENV['INSTAGRAM_GRAPH_VERSION'] = '';

        $this->assertNull(Environment::get('INSTAGRAM_GRAPH_VERSION'));

        unset($_ENV['INSTAGRAM_GRAPH_VERSION']);
    }
}
