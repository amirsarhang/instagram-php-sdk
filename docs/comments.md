# Comments

Everything on `$instagram->comments()`. Needs the
`instagram_business_manage_comments` permission.

## Read a comment

```php
$comment = $instagram->comments()->get($commentId);

// ['timestamp' => '2026-08-28T10:45:00+0000', 'text' => 'Nice!', 'id' => '17900000000000000']
```

`timestamp` and `text` come back by default. Ask for other fields with the
second argument:

```php
$instagram->comments()->get($commentId, ['media', 'like_count', 'username']);
```

Pass an empty array to let Instagram return its own defaults:

```php
$instagram->comments()->get($commentId, []);
```

## Reply to a comment

```php
$instagram->comments()->reply($commentId, 'Thanks for watching!');

// ['id' => '17900000000000001']
```

The first argument is the node being replied to, so this also works with a
media ID to leave a top level comment.

## Hide and unhide

```php
$instagram->comments()->hide($commentId);         // hidden
$instagram->comments()->hide($commentId, false);  // visible again
```

## Delete

```php
$instagram->comments()->delete($commentId);

// ['success' => true]
```

Deleting is permanent. Hiding is usually the better choice for moderation.

## Reacting to new comments

To be told about comments as they happen rather than polling, subscribe to the
`comments` webhook field. See [Webhooks](webhooks.md).
