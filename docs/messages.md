# Messages

Instagram direct messages, on `$instagram->messages()`. Needs the
`instagram_business_manage_messages` permission.

Meta only lets you message a user inside the 24 hour window after their last
message to you, unless you are using an approved message tag.

## Read a message

```php
$message = $instagram->messages()->get($messageId);

// ['message' => 'Hi', 'from' => [...], 'created_time' => '...', 'id' => '...']
```

`message`, `from`, `created_time` and `attachments` come back by default:

```php
$instagram->messages()->get($messageId, ['message', 'from']);
```

## Send text

```php
$instagram->messages()->sendText($userId, 'Hello there');

// ['recipient_id' => '17841400000000000', 'message_id' => 'aWdfZAG1...']
```

`$userId` is the Instagram-scoped ID of the person, which you get from a webhook
event or from the `from` field of a message.

## Send media

```php
use Amirsarhang\AttachmentType;

$instagram->messages()->sendMedia($userId, 'https://example.com/photo.jpg');

$instagram->messages()->sendMedia(
    $userId,
    'https://example.com/clip.mp4',
    AttachmentType::VIDEO,
);
```

`AttachmentType` holds the values Instagram accepts: `IMAGE`, `VIDEO` and
`AUDIO`. The parameter is a plain string, so `'video'` and
`AttachmentType::VIDEO` are interchangeable; the constants just save you
guessing the spelling.

The URL must be publicly reachable — Instagram fetches the file itself, so
signed URLs that expire quickly or hosts behind a firewall will fail.
