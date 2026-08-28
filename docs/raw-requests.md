# Raw requests

The SDK does not wrap every Graph endpoint. For anything it misses, send the
request yourself — authentication, the API version and error handling are still
taken care of.

```php
$media = $instagram->get('/me/media', ['fields' => 'id,caption,media_url']);

$instagram->post('/me/messages', [
    'recipient' => ['id' => $userId],
    'message' => ['text' => 'Hello'],
]);

$instagram->delete('/'.$commentId);
```

Every method takes the endpoint first, then the JSON body, then extra query
parameters:

```php
$instagram->post($endpoint, $body = [], $query = []);
```

## How endpoints are resolved

A relative endpoint is resolved against the versioned API root, so `'/me'`,
`'me'` and `'/me?fields=id'` all reach
`https://graph.instagram.com/v21.0/me`. A leading slash makes no difference.

An absolute URL is used as-is, which is how the SDK reaches the OAuth hosts:

```php
$instagram->graph()->get('https://api.instagram.com/oauth/access_token');
```

Query parameters given in the endpoint string and in the `$query` array are
merged, with the array winning. `null` values are dropped.

## The transport directly

`$instagram->graph()` returns the `GraphClient` the resources share, if you
would rather work one level down:

```php
$client = $instagram->graph();

$client->get('/me', ['fields' => 'username']);
$client->postForm($url, ['grant_type' => 'authorization_code']);
$client->withToken($anotherToken)->get('/me');
```

Every one of these returns the decoded response as an array, and throws on
anything that is not a successful JSON response. See
[Error handling](error-handling.md).
