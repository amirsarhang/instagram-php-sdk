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
      "amirsarhang/instagram-php-sdk": "2.0.0"
},
```


## Documentation

### Requirements

| PHP Version | Package Version |
|:-----------:|:---------------:|
|  `>= 7.0`   |      `1.x`      |
|  `>= 8.0`   |      `2.x`      |

****Please remember that you need verified Facebook APP to use this sdk.***
<br>
`FACEBOOK_APP_ID`
<br>
`FACEBOOK_APP_SECRET`

### Configuration

Put these values in your .env file:

```dotenv
FACEBOOK_APP_ID="<YOUR_FACEBOOK_APP_ID>" // Get it from your FB developer dashboard
FACEBOOK_APP_SECRET="<YOUR_FACEBOOK_APP_SECRET>" // Get it from your FB developer dashboard
FACEBOOK_GRAPH_VERSION="v10.0" // Your Graph version >= v10.0
INSTAGRAM_CALLBACK_URL="https://yoursite.com/instagram/callback" // Instagram callback after login
```

### Auth & Login

```php
use Amirsarhang\Instagram;
...
public function login()
{
    // Go to FB Documentations to see available permissions
    $permissions = [
        'instagram_basic',
        'pages_show_list',
        'instagram_manage_comments',
        'instagram_manage_messages',
        'pages_manage_engagement',
        'pages_read_engagement',
        'pages_manage_metadata'
    ];
    
    // Generate Instagram Graph Login URL
    $login = (new Instagram())->getLoginUrl($permissions);
    
    // Redirect To Facebook Login & Select Account Page
    return header("Location: ".$login);
}
```
* _**Please remember that your added permissions need verified by Facebook.**_

[Here](https://developers.facebook.com/docs/permissions/reference) you can find Facebook Permissions.

Generate & Save User Access Token in your Database.
```php
use Amirsarhang\Instagram;
...
public function callback()
{
    // Generate User Access Token After User Callback To Your Site
    return Instagram::getUserAccessToken();
}
```
Then we are ready to generate our page access token, but first we should get all selected page by user, then show
them to your user to select which page's access token should be save in Database.

```php
use Amirsarhang\Instagram;
...
public function instagramAccounts(): array
{
    $token = "<USER_ACCESS_TOKEN>"; // We got it in callback
    $instagram = new Instagram($token);

    // Will return all instagram accounts that connected to your facebook selected pages.
    return $instagram->getConnectedAccountsList(); 
}
```
### Sample Response
```
[
"success": "true",
"instagramAccounts": [
{
  "name": "Test Page",
  "biography": "This is Test account",
  "username": "username",
  "followers_count": 167000,
  "follows_count": 1,
  "media_count": 231,
  "profile_picture_url": "https://scontent.fist6-1.fna.fbcdn.net/v/123.jpg?_nc_cat=109&ccb=1-5&_nc_sid=86c713&_nc_ohc=Gnf5d6wdF3UAX8jVBUl&_nc_ht=scontent.fist6-1",
  "id": "17841111111111111",
  "fb_page_id": "108011111111111",
  "fb_page_access_token": "EAAWq8obOe68BAA1r8GUHqOvVZHDY&WFWKHBDfJrjPswLuMb4E8ZCxCRvNW37bt9tslaBBRbTv"
},
{
  "name": "Test Page2",
  "biography": "This is other Test account",
  "username": "username2",
  "followers_count": 1200,
  "follows_count": 22,
  "media_count": 23,
  "profile_picture_url": "https://scontent.fist6-1.fna.fbcdn.net/v/456.jpg?_nc_cat=109&ccb=1-5&_nc_sid=86c713&_nc_ohc=Gnf5d6wdF3UAX8jVBUl&_nc_ht=scontent.fist6-1",
  "id": "17841222222222222",
  "fb_page_id": "108022222222222",
  "fb_page_access_token": "XXREWDY&WFWKHBDfJrjPswLuMb4E8ZCxCRvNW37bt9tslBCFEZDZD"
}]
]
```
After storing selected page data by user in your database, then you need to call `subscribeWebhook()` to register this page for get real time Events.
```php
use Amirsarhang\Instagram;
...
public function registerWebhook()
{
    $token = "<FACEBOOK_PAGE_ACCESS_TOKEN>";
    $fb_page_id= "<FACEBOOK_PAGE_ID>";
    $instagram = new Instagram($token);

    // Default subscribe with email field
    return $instagram->subscribeWebhook($fb_page_id, $token);
    
    // You can pass your needed fields as an Array in the last parameter.
    // Your app does not receive notifications for changes to a field
    // unless you configure Page subscriptions in the App Dashboard and subscribe to that field.
    return $instagram->subscribeWebhook($fb_page_id, $token, ["email", "feed", "mentions"]);
}
```
Check this [link](https://developers.facebook.com/micro_site/url/?click_from_context_menu=true&country=TR&destination=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fgraph-api%2Fwebhooks%2Fgetting-started%2Fwebhooks-for-instagram%23step-2--enable-page-subscriptions&event_type=click&last_nav_impression_id=0v2LwuGJA3Ewl6NHx&max_percent_page_viewed=99&max_viewport_height_px=1041&max_viewport_width_px=1792&orig_http_referrer=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fgraph-api%2Fwebhooks%2Fgetting-started%2Fwebhooks-for-instagram&orig_request_uri=https%3A%2F%2Fdevelopers.facebook.com%2Fajax%2Fdocs%2Fnav%2F%3Fpath1%3Dgraph-api%26path2%3Dwebhooks%26path3%3Dgetting-started%26path4%3Dwebhooks-for-instagram&region=emea&scrolled=true&session_id=1JPBKkD9blH7vdvMk&site=developers) for more details about page subscriptions.

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
* If your request is on graphEdge, you can pass `true` on `$instagram->get($endpoint, true)` as second parameter.

## Methods

### _Comment Methods_

### Get Comment Data
```php
// Get default Comment fields data (Timestamp, text, id)
$get_comment = $instagram->getComment($comment_id);

// If you need other fields you can send them as array
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

// If you need other fields you can send them as array
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
