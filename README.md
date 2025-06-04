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
      "amirsarhang/instagram-php-sdk": "3.0.0"
},
```


## Documentation

### Requirements

| PHP Version | Package Version |     Connection Type     |             Required Parameters             |
|:-----------:|:---------------:|:-----------------------:|:-------------------------------------------:|
|  `>= 7.0`   |      `1.x`      | `Facebook Graph Login`  |  `FACEBOOK_APP_ID \| FACEBOOK_APP_SECRET`   |
|  `>= 8.0`   |      `2.x`      | `Facebook Graph Login`  |  `FACEBOOK_APP_ID \| FACEBOOK_APP_SECRET`   |
|  `>= 8.0`   |      `3.x`      | `Instagram Graph Login` | `INSTAGRAM_APP_ID \| INSTAGRAM_APP_SECRET`  |

****Please remember that you need a verified Facebook APP to use this sdk.***
<br>

### Configuration

Put these values in your .env file:

```dotenv
INSTAGRAM_APP_ID="<YOUR_INSTAGRAM_APP_ID>" // Get it from your Meta developer dashboard
INSTAGRAM_APP_SECRET="<YOUR_INSTAGRAM_APP_SECRET>" // Get it from your Meta developer dashboard
INSTAGRAM_GRAPH_VERSION="v21.0" // Your Graph version >= v19.0
INSTAGRAM_CALLBACK_URL="https://yoursite.com/instagram/callback" // Instagram callback after login
```

### Auth & Login

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
    
    // Generate Instagram Graph Login URL
    $login = (new Instagram())->getLoginUrl($permissions);
    
    // Redirect To Instagram Login & Select Account Page
    return header("Location: ".$login);
}
```
* _**Please remember that your added permissions need verified by Meta.**_

[Here](https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login) you can find Meta Permissions.

Generate & Save Page Access Token in your Database.
```php
use Amirsarhang\Instagram;
...
public function callback()
{
    // Get 'code' query string from Callback URL (ex. /callback?code=AQD5...)
    $code = $_GET['code'];
    
    // Generate Page Access Token After User Callback To Your Site
    return Instagram::getPageAccessToken($code);
}
```
### Sample Response
```
{
  "access_token": "IGQWRNSElpaDlWa0h1OXjsDhr8V3o0RHg2c2MyS2VTbmlyZA3k4ZAF8yT0Vh...",
  "token_type": "bearer",
  "expires_in": 5180249, // Access token expire timestamp (about 2 months)
  "id": "1234567890123456", // Instagram page ID
  "name": "Test Page", // Instagram page name
  "username": "test_page" // Instagram page username
}
```
After storing selected page data by user in your database, then you need to call `subscribeWebhook()` to register this page for get real time Events.
```php
use Amirsarhang\Instagram;
...
public function registerWebhook()
{
    $token = "<ACCESS_TOKEN>";
    $instagram_page_id= "<INSTAGRAM_PAGE_ID>";
    $instagram = new Instagram($token);

    // Default subscribe with "messages" field
    return $instagram->subscribeWebhook($instagram_page_id, $token);
    
    // You can pass your necessary fields as an Array in the last parameter.
    // Your app does not receive notifications for changes to a field
    // unless you configure Page subscriptions in the App Dashboard and subscribe to that field.
    return $instagram->subscribeWebhook($instagram_page_id, $token, ["messages", "comments"]);
}
```
Check this [link](https://developers.facebook.com/docs/graph-api/webhooks/reference/instagram/) for more details about page subscriptions.

### Usage
```php
use Amirsarhang\Instagram;
...
public function userInfo()
{

    $instagram = new Instagram($access_token);

    $endpoint = '/me?fields=id,name';

    return $instagram->get($endpoint);

}
```

## Methods

### _Comment Methods_

### Get Comment Data
```php
// Get default Comment fields data (Timestamp, text, id)
$get_comment = $instagram->getComment($comment_id);

// If you need other fields, you can send them as an array
$get_comment = $instagram->getComment($comment_id, ['media','like_count']);

return $get_comment;
```

### Add Comment
```php
return $instagram->addComment($recipient_id, 'Test Reply');
```

### Delete Comment
```php
return $instagram->deleteComment($comment_id);
```

### Hide & UnHide Comment
```php
return $instagram->hideComment($comment_id, true); // false for UnHide
```

### _Messaging Methods_

### Get Message Data
```php
// Get default Message fields data (message, from, created_time, attachments, id)
$get_message = $instagram->getMessage($message_id);

// If you need other fields, you can send them as an array
$get_message = $instagram->getMessage($message_id, ['attachments','from']);

return $get_message;
```

### Send Text Message (Direct Message)
```php
return $instagram->addTextMessage($recipient_id, 'Test DM');
```

### Send Media Message (Direct Message)
```php
return $instagram->addMediaMessage($recipient_id, '<IMAGE_URL>');
```

I will add more Useful methods as soon as possible :)

Check out the [documentation website][documentation] for detailed information
and code examples.


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
