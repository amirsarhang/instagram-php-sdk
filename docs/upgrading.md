# Upgrading to 4.x

4.x reorganises the SDK around resource groups, replaces the Guzzle dependency
with PSR-18, and turns every failure into an exception.

Your 3.x method names still work — they forward to the new API — so most
applications upgrade by changing the PHP requirement and nothing else. The
exceptions are listed under [Breaking changes](#breaking-changes).

## Requirements

PHP 8.0 or newer, unchanged from 3.x. You also need a PSR-18 HTTP client; if you
had 3.x working, Guzzle is already installed and nothing changes:

```bash
composer require guzzlehttp/guzzle
```

`vlucas/phpdotenv` is now optional. Install it if you rely on the SDK reading a
`.env` file itself:

```bash
composer require vlucas/phpdotenv
```

## Breaking changes

### `get()`, `post()` and `delete()` take the endpoint first

```php
// 3.x
$instagram->post($params, '/me/messages');
$instagram->delete($params, '/'.$commentId);

// 4.x
$instagram->post('/me/messages', $params);
$instagram->delete('/'.$commentId, $params);
```

The argument types differ, so a missed call site is a `TypeError` at the call,
not a silent misbehaviour.

### Failures throw instead of returning `false`

`getPageAccessToken()` and `getConnectedAccount()` returned `false` when
something went wrong. They now throw, like everything else:

```php
// 3.x
$token = Instagram::getPageAccessToken($code);
if ($token === false) {
    return $this->loginFailed();
}

// 4.x
try {
    $token = (new Instagram())->oauth()->connect($code);
} catch (InstagramException $e) {
    return $this->loginFailed($e);
}
```

This is the change most likely to need attention: a `false` check that used to
catch a failure now never fires, and the exception propagates.

### `getPageAccessToken()` is no longer static

```php
// 3.x
Instagram::getPageAccessToken($code);

// 4.x
(new Instagram())->oauth()->connect($code);
```

`Instagram::getUserInfo()` is gone for the same reason; use
`(new Instagram($token))->account()->me()`.

### Endpoints are always versioned

In 3.x a leading slash made Guzzle discard the API version, so `/me/messages`
went to `graph.instagram.com/me/messages` while `me/messages` went to
`graph.instagram.com/v21.0/me/messages`. Every endpoint is now resolved against
the versioned root.

### Renamed and removed classes

| 3.x | 4.x |
|:--|:--|
| `Amirsarhang\InstagramException` | `Amirsarhang\Exception\InstagramException` (old name still works) |
| `Amirsarhang\InstagramGraphLogin` | `Amirsarhang\Auth\OAuth` |
| `Amirsarhang\InstagramPayloads` | `Amirsarhang\Http\GraphClient` |
| `Amirsarhang\Resource\*` | `Amirsarhang\Resources\*` |

`Amirsarhang\InstagramException` is aliased to the new class, so existing catch
blocks keep working. `InstagramGraphLogin` and `InstagramPayloads` are gone;
their replacements have different method names and signatures.

## Deprecated methods

These still work and are covered by tests, but will be removed in 5.x:

| Deprecated | Replacement |
|:--|:--|
| `getLoginUrl($permissions)` | `oauth()->loginUrl($permissions)` |
| `getPageAccessToken($code)` | `oauth()->connect($code)` |
| `getConnectedAccount($token)` | `withToken($token)->account()->me()` |
| `subscribeWebhook($token, $fields)` | `withToken($token)->webhooks()->subscribe($fields)` |
| `getComment($id, $fields)` | `comments()->get($id, $fields)` |
| `addComment($id, $message)` | `comments()->reply($id, $message)` |
| `hideComment($id, $status)` | `comments()->hide($id, $status)` |
| `deleteComment($id)` | `comments()->delete($id)` |
| `getMessage($id, $fields)` | `messages()->get($id, $fields)` |
| `addTextMessage($id, $text)` | `messages()->sendText($id, $text)` |
| `addMediaMessage($id, $url, $type)` | `messages()->sendMedia($id, $url, $type)` |

## What you gain

* Configuration through a [`Config` object](installation.md#configuration),
  so the SDK fits a framework without touching `$_ENV`.
* [Graph error details](error-handling.md#reading-a-graph-error) — status,
  error type, code, subcode and trace ID — instead of a string.
* Any PSR-18 client, so the SDK no longer dictates your Guzzle version.
* `oauth()->refreshToken()` for extending long lived tokens.
* `webhooks()->subscriptions()` and `webhooks()->unsubscribe()`.
* `withToken()` for handling several accounts from one instance.
