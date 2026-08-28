# Webhooks

Subscribing the connected account to real time events, on
`$instagram->webhooks()`.

Subscribing here is only half the job: the fields must also be configured for
your app in the
[Meta App Dashboard](https://developers.facebook.com/docs/graph-api/webhooks/getting-started).
Your app receives nothing for a field that is not enabled in both places.

## Subscribe

Do this once, right after storing a new access token:

```php
$instagram = new Instagram($accessToken);

$instagram->webhooks()->subscribe();                          // messages
$instagram->webhooks()->subscribe(['messages', 'comments']);  // both

// ['success' => true]
```

## Check what is subscribed

```php
$instagram->webhooks()->subscriptions();

// ['data' => [['subscribed_fields' => ['messages', 'comments'], ...]]]
```

## Unsubscribe

```php
$instagram->webhooks()->unsubscribe();
```

## Available fields

`messages`, `comments`, `live_comments`, `message_reactions`, `messaging_seen`
and others; the current list is in
[Meta's webhook reference](https://developers.facebook.com/docs/graph-api/webhooks/reference/instagram/).
