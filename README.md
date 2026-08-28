# amirsarhang/instagram-php-sdk

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][packagist]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
[![Total Downloads][badge-downloads]][downloads]

It's Instagram Graph SDK for PHP. 

With this package you can easily make all requests to Instagram Graph API, like Auth and CRUD. Also, we will have more methods regularly.

This project adheres to a [Contributor Code of Conduct][conduct]. By
participating in this project and its community, you are expected to uphold this
code.


## Installation

The preferred method of installation is via [Composer][]. Run the following
command to install the package and add it as a requirement to your project's
`composer.json`:

```bash
composer require amirsarhang/instagram-php-sdk
```
Or add the following to your composer.json file:
```bash
"require": {
      "amirsarhang/instagram-php-sdk": "^4.0"
},
```


## Documentation

### Requirements

| PHP Version | Package Version |     Connection Type     |             Required Parameters             |
|:-----------:|:---------------:|:-----------------------:|:-------------------------------------------:|
|  `>= 7.0`   |      `1.x`      | `Facebook Graph Login`  |  `FACEBOOK_APP_ID \| FACEBOOK_APP_SECRET`   |
|  `>= 8.0`   |      `2.x`      | `Facebook Graph Login`  |  `FACEBOOK_APP_ID \| FACEBOOK_APP_SECRET`   |
|  `>= 8.0`   |      `3.x`      | `Instagram Graph Login` | `INSTAGRAM_APP_ID \| INSTAGRAM_APP_SECRET`  |
|  `>= 8.0`   |      `4.x`      | `Instagram Graph Login` | `INSTAGRAM_APP_ID \| INSTAGRAM_APP_SECRET`  |

****Please remember that you need a verified Facebook APP to use this sdk.***
<br>

### Configuration

The SDK sends requests through any [PSR-18](https://www.php-fig.org/psr/psr-18/)
HTTP client. If your project does not have one yet:

```bash
composer require guzzlehttp/guzzle
```

Put these values in your .env file:

```dotenv
INSTAGRAM_APP_ID="<YOUR_INSTAGRAM_APP_ID>" // Get it from your Meta developer dashboard
INSTAGRAM_APP_SECRET="<YOUR_INSTAGRAM_APP_SECRET>" // Get it from your Meta developer dashboard
INSTAGRAM_CALLBACK_URL="https://yoursite.com/instagram/callback" // Instagram callback after login
INSTAGRAM_GRAPH_VERSION="v21.0" // Optional, defaults to v21.0
```

Reading a `.env` file needs `vlucas/phpdotenv`; without it the SDK still reads
`$_ENV`, `$_SERVER` and `getenv()`. Inside a framework, skip the environment and
pass a `Config` instead:

```php
use Amirsarhang\Config;
use Amirsarhang\Instagram;

$config = new Config(
    appId: config('instagram.app_id'),
    appSecret: config('instagram.app_secret'),
    redirectUri: config('instagram.callback_url'),
);

$instagram = new Instagram($accessToken, $config);
```

The three credentials are only needed for the login flow. Calls made with an
access token you already hold require none of them.

### Login

```php
use Amirsarhang\Instagram;
...
public function login()
{
    // Go to Meta Documentations to see available permissions
    $permissions = [
        'instagram_business_basic',
        'instagram_business_manage_messages',
        'instagram_business_manage_comments',
    ];

    $url = (new Instagram())->oauth()->loginUrl($permissions);

    return header("Location: ".$url);
}
```
* _**Please remember that your added permissions need verified by Meta.**_

[Here](https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login) you can find Meta Permissions.

Generate & Save the Access Token in your database.

```php
use Amirsarhang\Instagram;
...
public function callback()
{
    // Get 'code' query string from Callback URL (ex. /callback?code=AQD5...)
    return (new Instagram())->oauth()->connect($_GET['code']);
}
```

### Sample Response
```
{
  "access_token": "IGQWRNSElpaDlWa0h1OXjsDhr8V3o0RHg2c2MyS2VTbmlyZA3k4ZAF8yT0Vh...",
  "token_type": "bearer",
  "expires_in": 5183944, // Access token expire timestamp (about 2 months)
  "id": "1234567890123456", // Instagram page ID
  "name": "Test Page", // Instagram page name
  "username": "test_page" // Instagram page username
}
```

Long lived tokens last about 60 days. Refresh one before it expires:

```php
$instagram->oauth()->refreshToken($currentToken);
```

After storing the account, call `subscribe()` to start receiving real time events.

```php
use Amirsarhang\Instagram;
...
public function registerWebhook()
{
    $instagram = new Instagram("<ACCESS_TOKEN>");

    // Default subscribe with "messages" field
    return $instagram->webhooks()->subscribe();

    // Or pass the fields you need.
    // Your app does not receive notifications for changes to a field
    // unless you configure Page subscriptions in the App Dashboard and subscribe to that field.
    return $instagram->webhooks()->subscribe(["messages", "comments"]);
}
```

Check this [link](https://developers.facebook.com/docs/graph-api/webhooks/reference/instagram/) for more details about page subscriptions.

### Usage
```php
use Amirsarhang\Instagram;
...
public function userInfo()
{
    $instagram = new Instagram($accessToken);

    return $instagram->account()->me();
}
```

## Methods

### _Comment Methods_

```php
// Get default Comment fields data (timestamp, text, id)
$instagram->comments()->get($comment_id);

// If you need other fields, you can send them as an array
$instagram->comments()->get($comment_id, ['media', 'like_count']);

// Reply to a comment, or comment on a media object
$instagram->comments()->reply($comment_id, 'Test Reply');

// Hide & UnHide
$instagram->comments()->hide($comment_id);
$instagram->comments()->hide($comment_id, false);

$instagram->comments()->delete($comment_id);
```

### _Messaging Methods_

```php
use Amirsarhang\AttachmentType;

// Get default Message fields data (message, from, created_time, attachments, id)
$instagram->messages()->get($message_id);

// If you need other fields, you can send them as an array
$instagram->messages()->get($message_id, ['attachments', 'from']);

$instagram->messages()->sendText($recipient_id, 'Test DM');

$instagram->messages()->sendMedia($recipient_id, '<IMAGE_URL>');
$instagram->messages()->sendMedia($recipient_id, '<VIDEO_URL>', AttachmentType::VIDEO);
```

### _Webhook Methods_

```php
$instagram->webhooks()->subscribe(['messages', 'comments']);
$instagram->webhooks()->subscriptions();
$instagram->webhooks()->unsubscribe();
```

### _Raw Requests_

For endpoints this SDK does not wrap yet:

```php
$instagram->get('/me/media', ['fields' => 'id,caption,media_url']);
$instagram->post($endpoint, $params);
$instagram->delete($endpoint);
```

### _Error Handling_

Every failure throws. Catch `InstagramException` for everything, or
`GraphException` when you need the details Instagram sent back:

```php
use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\InstagramException;
...
try {
    $instagram->comments()->reply($comment_id, 'Thanks!');
} catch (GraphException $e) {
    if ($e->errorCode() === 190) {
        return $this->requireReauthentication();
    }

    report($e->getMessage().' (trace '.$e->traceId().')');
} catch (InstagramException $e) {
    report($e->getMessage());
}
```

I will add more Useful methods as soon as possible :)

### _Using Your Own HTTP Client_

The constructor accepts any PSR-18 client, which is where middleware, proxies,
retries and timeouts belong:

```php
use Amirsarhang\Instagram;
use GuzzleHttp\Client;

$instagram = new Instagram($token, null, new Client(['timeout' => 10]));
```

### _Multiple Accounts_

`withToken()` returns a copy for another account, reusing the same HTTP client
and configuration:

```php
$other = $instagram->withToken($anotherAccountToken);
```

## Upgrading from 3.x

Your 3.x method names still work; they forward to the new API. The full list of
changes is in [UPGRADE.md](UPGRADE.md).

Check out the [documentation website][documentation] for detailed information
and code examples.


## Testing

```bash
composer install
composer run test
```


## Contributing

Contributions are welcome! Please read [CONTRIBUTING][] for details.


## Copyright and License

The amirsarhang/instagram-php-sdk library is copyright © [Amirhossein Sarhangian]()
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for
more information.


[conduct]: https://github.com/amirsarhang/instagram-php-sdk/blob/master/.github/CODE_OF_CONDUCT.md
[composer]: http://getcomposer.org/
[documentation]: https://amirsarhang.github.io/instagram-php-sdk/
[contributing]: https://github.com/amirsarhang/instagram-php-sdk/blob/master/.github/CONTRIBUTING.md

[badge-source]: http://img.shields.io/badge/source-amirsarhang/instagram--php--sdk-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/packagist/v/amirsarhang/instagram-php-sdk.svg?style=flat-square&label=release
[badge-license]: https://img.shields.io/packagist/l/amirsarhang/instagram-php-sdk.svg?style=flat-square
[badge-php]: https://img.shields.io/packagist/php-v/amirsarhang/instagram-php-sdk.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/amirsarhang/instagram-php-sdk.svg?style=flat-square&colorB=mediumvioletred

[source]: https://github.com/amirsarhang/instagram-php-sdk
[packagist]: https://packagist.org/packages/amirsarhang/instagram-php-sdk
[license]: https://github.com/amirsarhang/instagram-php-sdk/blob/master/LICENSE
[php]: https://php.net
[downloads]: https://packagist.org/packages/amirsarhang/instagram-php-sdk
