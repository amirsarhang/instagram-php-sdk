# Account

The Instagram account behind the current access token, on
`$instagram->account()`.

```php
$profile = $instagram->account()->me();
```

```json
{
  "user_id": "17841400000000000",
  "name": "Test Page",
  "biography": "We sell things",
  "username": "test_page",
  "followers_count": 1234,
  "follows_count": 56,
  "profile_picture_url": "https://scontent.cdninstagram.com/...",
  "website": "https://example.com",
  "id": "1234567890123456"
}
```

Ask for a narrower set of fields when you do not need all of it:

```php
$instagram->account()->me(['username', 'followers_count']);
```

To read a different account, swap the token first:

```php
$instagram->withToken($otherAccountToken)->account()->me();
```
