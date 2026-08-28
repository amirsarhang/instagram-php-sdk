<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit\Resources;

use Amirsarhang\Resources\Account;
use Amirsarhang\Tests\TestCase;

final class AccountTest extends TestCase
{
    public function testMeRequestsTheProfileFieldsByDefault(): void
    {
        $this->http->queue($this->json(['username' => 'test_page']));

        $result = $this->account()->me();

        $this->assertSame(['username' => 'test_page'], $result);
        $this->assertSame(self::BASE_URI . 'me', $this->url());
        $this->assertSame(implode(',', Account::DEFAULT_FIELDS), $this->query()['fields']);
    }

    public function testMeAcceptsCustomFields(): void
    {
        $this->http->queue($this->json([]));

        $this->account()->me(['id', 'username']);

        $this->assertSame(['fields' => 'id,username'], $this->query());
    }

    private function account(): Account
    {
        return new Account($this->graphClient());
    }
}
