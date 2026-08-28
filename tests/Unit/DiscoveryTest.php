<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit;

use Amirsarhang\Config;
use Amirsarhang\Http\GraphClient;
use Amirsarhang\Instagram;
use Amirsarhang\Tests\TestCase;

/**
 * The SDK must work without being handed an HTTP client, by discovering
 * whichever PSR-18 implementation the host application already has.
 */
final class DiscoveryTest extends TestCase
{
    public function testItFindsAPsr18ClientOnItsOwn(): void
    {
        $this->expectNotToPerformAssertions();

        new GraphClient(self::BASE_URI, self::TOKEN);
    }

    public function testTheClientCanBeBuiltWithNothingButAToken(): void
    {
        $this->expectNotToPerformAssertions();

        new Instagram(self::TOKEN);
    }

    public function testItNeedsNoCredentialsForPlainApiCalls(): void
    {
        $instagram = new Instagram(self::TOKEN, new Config());

        $this->assertSame('https://graph.instagram.com/v21.0/', $instagram->config()->graphBaseUri());
    }
}
