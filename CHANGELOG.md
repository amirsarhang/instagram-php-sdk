# amirsarhang/instagram-php-sdk Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## 4.x

### 4.0.0

Still requires PHP 8.0, same as 3.x. Your 3.x method names still work — they
forward to the new API — so most applications upgrade without touching their
call sites. See [UPGRADE.md](UPGRADE.md) for the full migration.

#### Added

* Resource groups: `comments()`, `messages()`, `webhooks()`, `account()`,
  `oauth()` and `graph()`.
* `Config`, an immutable value object holding the credentials and Graph version,
  so the SDK can be configured without touching `$_ENV`.
* `oauth()->refreshToken()` to extend a long lived token before it expires.
* `webhooks()->subscriptions()` and `webhooks()->unsubscribe()`.
* `withToken()` on both `Instagram` and `GraphClient`, for handling several
  accounts from one instance.
* An exception hierarchy under `Amirsarhang\Exception`: `ConfigurationException`,
  `InvalidArgumentException`, `TransportException` and `GraphException`.
* `GraphException` exposes the Graph error envelope: `statusCode()`,
  `errorType()`, `errorCode()`, `errorSubcode()`, `traceId()`, `error()`
  and `body()`.
* `AttachmentType` with the attachment values Instagram accepts.
* A documentation site under `docs/`, replacing the GitHub Pages placeholder.
* A `composer run compat` script that holds the PHP 8.0 floor, wired into CI
  alongside a test matrix running 8.0 through 8.4.

#### Changed

* HTTP now goes through PSR-18 and PSR-17 with `php-http/discovery`, so any
  client works and the SDK no longer dictates your Guzzle version. Guzzle moved
  from `require` to `suggest`.
* `get()`, `post()` and `delete()` take the endpoint first, then the body, then
  query parameters. The argument types differ from 3.x, so a missed call site
  fails loudly with a `TypeError`.
* `getPageAccessToken()` and `getConnectedAccount()` throw instead of returning
  `false`.
* `InstagramPayloads` became `Http\GraphClient` and no longer extends
  `Instagram`; the transport and the API surface are separate.
* `InstagramGraphLogin` became `Auth\OAuth`.
* The resource namespace is `Amirsarhang\Resources`, not `Amirsarhang\Resource`;
  `Resource` is a soft-reserved word in PHP.
* Exceptions moved to `Amirsarhang\Exception`. `Amirsarhang\InstagramException`
  is aliased to the new base class, so existing catch blocks keep working.
* Argument validation names the method and the argument that was empty, instead
  of a generic message.
* `vlucas/phpdotenv` moved from `require` to `suggest`; the SDK reads `$_ENV`,
  `$_SERVER` and `getenv()` without it.
* OAuth credentials are validated when a call needs them, so requests made with
  an existing access token no longer require an app ID or secret.

#### Removed

* `Instagram::getUserInfo()`; use `account()->me()`.
* `error_log()` calls that duplicated every thrown exception.

#### Deprecated

* The 3.x method names on `Instagram`, listed in [UPGRADE.md](UPGRADE.md). They
  are covered by tests and will be removed in 5.x.


## 3.x

### 3.1.0

* Add a test suite covering the login flow, the payload layer and every SDK method.
* Add `guzzlehttp/guzzle` to `require`; it was used by the SDK but never declared.
* Replace the `echo` and `exit` in the payload layer with an `InstagramException`
  that keeps the Guzzle exception as its previous exception.
* Accept an optional `GuzzleHttp\ClientInterface` on every entry point.
* Resolve every endpoint against the versioned Graph API base URI, so a leading
  slash no longer drops the version.
* Throw a descriptive `InstagramException` when an `INSTAGRAM_*` variable is
  missing, and fall back to `v21.0` when `INSTAGRAM_GRAPH_VERSION` is not set.
* Locate the `.env` file by walking up from the package instead of assuming a
  fixed `vendor/` depth.
* Widen `getConnectedAccount()` to `array|bool`; it could already return `false`.
* Replace Travis CI with GitHub Actions.


## 1.x

### 1.0.0 (2021-10-02)

* Initial release.


[1.0.0]: https://github.com/amirsarhang/instagram-php-sdk/commits/1.0.0
