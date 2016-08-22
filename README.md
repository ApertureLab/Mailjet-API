# Mailjet-API

*Mailjet-API* provides a simple PHP library for the [Mailjet API](http://www.mailjet.com/docs/api) v1.

## Dependencies

* PHP 5.4+
* [zend-http](https://github.com/zendframework/zend-http)

Note: There is also a version available for the [Zend Framework 1](https://github.com/Narno/Mailjet-API/tree/zf1).

## Installation

The recommended way is through [Composer](https://getcomposer.org).
```
$ composer require narno/mailjet-api
```

## Usage

```php
<?php
use Narno\Mailjet\Api as MailjetApi;

try {
    $api = New MailjetApi('key', 'secret_key');
    // fetches user's infos...
    $userInfos = $api->user->infos();
    if ($userInfos->status == 'OK') {
        // ...and displays
        print_r($userInfos->infos);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
```

Mailjet API documentation: http://www.mailjet.com/docs/api.

## License

*Mailjet-API* is released under the terms of the [MIT license](http://opensource.org/licenses/MIT).

Copyright (c) 2012-2016 Arnaud Ligny
