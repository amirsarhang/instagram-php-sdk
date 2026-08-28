<?php

declare(strict_types=1);

namespace Amirsarhang\Tests\Unit\Resources;

use Amirsarhang\Exception\InvalidArgumentException;
use Amirsarhang\Resources\Comments;
use Amirsarhang\Tests\TestCase;

final class CommentsTest extends TestCase
{
    public function testGetRequestsTimestampAndTextByDefault(): void
    {
        $this->http->queue($this->json(['text' => 'Nice!']));

        $result = $this->comments()->get('17900000000000000');

        $this->assertSame(['text' => 'Nice!'], $result);
        $this->assertSame(self::BASE_URI . '17900000000000000', $this->url());
        $this->assertSame(['fields' => 'timestamp,text'], $this->query());
    }

    public function testGetAcceptsCustomFields(): void
    {
        $this->http->queue($this->json([]));

        $this->comments()->get('17900000000000000', ['id', 'username']);

        $this->assertSame(['fields' => 'id,username'], $this->query());
    }

    public function testGetOmitsTheFieldsQueryWhenNoneAreWanted(): void
    {
        $this->http->queue($this->json([]));

        $this->comments()->get('17900000000000000', []);

        $this->assertSame('', $this->request()->getUri()->getQuery());
    }

    public function testReplyPostsTheMessageToTheRepliesEdge(): void
    {
        $this->http->queue($this->json(['id' => '17900000000000001']));

        $this->comments()->reply('17900000000000000', 'Thanks!');

        $this->assertSame('POST', $this->request()->getMethod());
        $this->assertSame(self::BASE_URI . '17900000000000000/replies', $this->url());
        $this->assertSame(['message' => 'Thanks!'], $this->requestBody());
    }

    public function testHideDefaultsToHiding(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->comments()->hide('17900000000000000');

        $this->assertSame(self::BASE_URI . '17900000000000000', $this->url());
        $this->assertSame(['hide' => true], $this->requestBody());
    }

    public function testHideCanUnhide(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->comments()->hide('17900000000000000', false);

        $this->assertSame(['hide' => false], $this->requestBody());
    }

    public function testDeleteTargetsTheComment(): void
    {
        $this->http->queue($this->json(['success' => true]));

        $this->comments()->delete('17900000000000000');

        $this->assertSame('DELETE', $this->request()->getMethod());
        $this->assertSame(self::BASE_URI . '17900000000000000', $this->url());
    }

    public function testItRejectsIncompleteArguments(): void
    {
        $comments = $this->comments();

        $this->assertRejects(
            fn () => $comments->get(''),
            'Comments::get() requires a non-empty $commentId.'
        );
        $this->assertRejects(
            fn () => $comments->reply('', 'Hello'),
            'Comments::reply() requires a non-empty $commentId.'
        );
        $this->assertRejects(
            fn () => $comments->reply('17900000000000000', ''),
            'Comments::reply() requires a non-empty $message.'
        );
        $this->assertRejects(
            fn () => $comments->hide(''),
            'Comments::hide() requires a non-empty $commentId.'
        );
        $this->assertRejects(
            fn () => $comments->delete(''),
            'Comments::delete() requires a non-empty $commentId.'
        );
    }

    public function testItSendsNoRequestWhenArgumentsAreIncomplete(): void
    {
        try {
            $this->comments()->reply('', '');
        } catch (InvalidArgumentException) {
            // Expected; the assertion below is what this test is about.
        }

        $this->assertSame(0, $this->http->count());
    }

    private function comments(): Comments
    {
        return new Comments($this->graphClient());
    }
}
