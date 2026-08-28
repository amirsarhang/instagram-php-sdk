# Instagram PHP SDK

An unofficial PHP SDK for the Instagram Graph API, covering Instagram Business
Login, comments, direct messages and webhook subscriptions.

```bash
composer require amirsarhang/instagram-php-sdk
```

```php
use Amirsarhang\Instagram;

$instagram = new Instagram($accessToken);

$instagram->comments()->reply($commentId, 'Thanks!');
$instagram->messages()->sendText($userId, 'Hello there');
$instagram->webhooks()->subscribe(['messages', 'comments']);
```

## Documentation

| Page | What it covers |
|:--|:--|
| [Installation](installation.md) | Requirements, the HTTP client, and configuration |
| [Authentication](authentication.md) | Business Login, access tokens, refreshing |
| [Comments](comments.md) | Reading, replying, hiding and deleting |
| [Messages](messages.md) | Reading and sending direct messages |
| [Webhooks](webhooks.md) | Subscribing to real time events |
| [Account](account.md) | The profile behind the access token |
| [Raw requests](raw-requests.md) | Endpoints the SDK does not wrap yet |
| [Error handling](error-handling.md) | The exception hierarchy and Graph error codes |
| [Upgrading to 4.x](upgrading.md) | What changed and how to migrate |

## Requirements

| PHP Version | Package Version |     Connection Type     |             Required Parameters             |
|:-----------:|:---------------:|:-----------------------:|:-------------------------------------------:|
|  `>= 7.0`   |      `1.x`      | `Facebook Graph Login`  |  `FACEBOOK_APP_ID \| FACEBOOK_APP_SECRET`   |
|  `>= 8.0`   |      `2.x`      | `Facebook Graph Login`  |  `FACEBOOK_APP_ID \| FACEBOOK_APP_SECRET`   |
|  `>= 8.0`   |      `3.x`      | `Instagram Graph Login` | `INSTAGRAM_APP_ID \| INSTAGRAM_APP_SECRET`  |
|  `>= 8.0`   |      `4.x`      | `Instagram Graph Login` | `INSTAGRAM_APP_ID \| INSTAGRAM_APP_SECRET`  |

You need a verified Meta app to use this SDK in production.

## Links

* [GitHub repository](https://github.com/amirsarhang/instagram-php-sdk)
* [Issue tracker](https://github.com/amirsarhang/instagram-php-sdk/issues)
* [Meta's Instagram Platform docs](https://developers.facebook.com/docs/instagram-platform)
