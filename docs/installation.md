# Installation

```bash
composer require amirsarhang/instagram-php-sdk
```

## The HTTP client

The SDK talks HTTP through [PSR-18](https://www.php-fig.org/psr/psr-18/), so it
works with whichever client your application already has. If you do not have
one, install Guzzle:

```bash
composer require guzzlehttp/guzzle
```

The client is discovered automatically. To pass one explicitly, hand any PSR-18
implementation to the constructor:

```php
use Amirsarhang\Instagram;
use GuzzleHttp\Client;

$instagram = new Instagram($accessToken, null, new Client([
    'timeout' => 10,
]));
```

This is also where middleware, proxies, retries and logging belong: configure
them on your own client and the SDK will use them.

## Configuration

`Config` holds your app credentials and the Graph API version. Pass it
explicitly, which is what you want inside a framework:

```php
use Amirsarhang\Config;
use Amirsarhang\Instagram;

$config = new Config(
    appId: config('instagram.app_id'),
    appSecret: config('instagram.app_secret'),
    redirectUri: config('instagram.callback_url'),
    graphVersion: 'v21.0',
);

$instagram = new Instagram($accessToken, $config);
```

`Config` is immutable; read it back with `appId()`, `appSecret()`,
`redirectUri()`, `graphVersion()` and `graphBaseUri()`.

Or leave it out and the SDK reads the environment:

```php
$instagram = new Instagram($accessToken);
```

### Environment variables

```dotenv
INSTAGRAM_APP_ID="<YOUR_INSTAGRAM_APP_ID>"
INSTAGRAM_APP_SECRET="<YOUR_INSTAGRAM_APP_SECRET>"
INSTAGRAM_CALLBACK_URL="https://yoursite.com/instagram/callback"
INSTAGRAM_GRAPH_VERSION="v21.0"
```

`INSTAGRAM_GRAPH_VERSION` is optional and defaults to `v21.0`. The three
credentials are only needed for the [login flow](authentication.md); calls made
with an access token you already hold need none of them.

To read a `.env` file, install [phpdotenv](https://github.com/vlucas/phpdotenv):

```bash
composer require vlucas/phpdotenv
```

Without it the SDK still reads `$_ENV`, `$_SERVER` and `getenv()`, which is all
a framework like Laravel or Symfony needs.

## Swapping tokens

An `Instagram` instance is tied to one access token. `withToken()` returns a
copy for another account, reusing the same HTTP client and configuration:

```php
$other = $instagram->withToken($anotherAccountToken);
```
