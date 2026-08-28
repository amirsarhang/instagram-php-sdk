# Error handling

Every failure is an exception. Nothing returns `false` to mean "something went
wrong", and nothing writes to the output buffer or calls `exit`.

## The hierarchy

```
Amirsarhang\Exception\InstagramException     (extends RuntimeException)
├── ConfigurationException    an app credential or setting is missing
├── InvalidArgumentException  an argument cannot produce a valid call
├── TransportException        the request never reached Instagram
└── GraphException            Instagram answered with an error
```

Catch the base class to handle everything:

```php
use Amirsarhang\Exception\InstagramException;

try {
    $instagram->comments()->reply($commentId, $text);
} catch (InstagramException $e) {
    report($e->getMessage());
}
```

## Reading a Graph error

`GraphException` carries the whole error envelope Instagram returned, so you can
branch on what actually went wrong:

```php
use Amirsarhang\Exception\GraphException;

try {
    $instagram->account()->me();
} catch (GraphException $e) {
    $e->statusCode();    // 401
    $e->errorType();     // 'OAuthException'
    $e->errorCode();     // 190
    $e->errorSubcode();  // 463
    $e->traceId();       // 'AbCdEfG' — quote this to Meta support
    $e->error();         // the full error array
    $e->body();          // the raw response body
}
```

`getCode()` returns the Graph error code when there is one, and the HTTP status
otherwise.

### Codes worth handling

| Code | Meaning | What to do |
|:--|:--|:--|
| `190` | Token expired or revoked | Send the user through login again |
| `4`, `17`, `32` | Rate limited | Back off and retry later |
| `10`, `200` | Missing permission | Check the scopes the token was granted |
| `100` | Bad parameter | A bug in the request; do not retry |

```php
if ($e->errorCode() === 190) {
    $this->requireReauthentication($account);
}
```

## Retrying

`TransportException` means the request never got an answer — a timeout, DNS
failure or refused connection. These are the ones worth retrying:

```php
use Amirsarhang\Exception\GraphException;
use Amirsarhang\Exception\TransportException;

try {
    $instagram->messages()->sendText($userId, $text);
} catch (TransportException $e) {
    $this->retryLater($e);           // never delivered, safe to send again
} catch (GraphException $e) {
    $this->reportRejection($e);      // Instagram said no, sending again will not help
}
```

The original PSR-18 exception is kept as `$e->getPrevious()`.

## Argument errors

`InvalidArgumentException` is thrown before any request is sent, so a rejected
call costs nothing and never half-completes:

```php
$instagram->comments()->reply('', 'Hello');
// Comments::reply() requires a non-empty $commentId.
```

## Configuration errors

`ConfigurationException` names the setting that is missing:

```php
(new Instagram())->oauth()->loginUrl(['instagram_business_basic']);
// Missing "INSTAGRAM_APP_ID" configuration. Set it in your .env file or pass it to the Config constructor.
```

Credentials are only checked when a call actually needs them, so requests made
with an existing access token do not require your app secret to be configured.
