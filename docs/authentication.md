# Authentication

The SDK implements
[Instagram Business Login](https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/business-login).
All of it lives on `$instagram->oauth()`.

## 1. Send the user to Instagram

```php
use Amirsarhang\Instagram;

public function login()
{
    $permissions = [
        'instagram_business_basic',
        'instagram_business_manage_messages',
        'instagram_business_manage_comments',
    ];

    $url = (new Instagram())->oauth()->loginUrl($permissions);

    return header('Location: '.$url);
}
```

Permissions must be approved by Meta before they work in production. The full
list is in
[Meta's documentation](https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login).

## 2. Handle the callback

Instagram redirects back to your `INSTAGRAM_CALLBACK_URL` with a `code`.
`connect()` runs the whole exchange: code to short lived token, short lived to
long lived, then the account the token belongs to.

```php
use Amirsarhang\Instagram;

public function callback()
{
    return (new Instagram())->oauth()->connect($_GET['code']);
}
```

```json
{
  "access_token": "IGQWRNSElpaDlWa0h1OXjsDhr8V3o0RHg2c2MyS2VTbmlyZA3k4ZAF8yT0Vh...",
  "token_type": "bearer",
  "expires_in": 5183944,
  "id": "1234567890123456",
  "name": "Test Page",
  "username": "test_page"
}
```

Pass your own field list as the second argument to change what comes back
alongside the token:

```php
$instagram->oauth()->connect($code, ['id', 'username', 'followers_count']);
```

Store `access_token` against the account and use it for every later call.

## Running the steps yourself

`connect()` is a convenience over three calls you can make individually:

```php
$oauth = (new Instagram())->oauth();

$shortLived = $oauth->exchangeCode($code);
$longLived  = $oauth->longLivedToken($shortLived['access_token']);
$account    = (new Instagram($longLived['access_token']))->account()->me();
```

## Keeping a token alive

Long lived tokens last about 60 days. Refresh one that is at least 24 hours old
to push the expiry back:

```php
$refreshed = $instagram->oauth()->refreshToken($currentToken);

// ['access_token' => '...', 'token_type' => 'bearer', 'expires_in' => 5183944]
```

A token that expires cannot be refreshed; the user has to log in again. Schedule
the refresh well before the 60 days are up.
