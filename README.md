# Mailjet-API

> Simple PHP library for the [Mailjet API](http://www.mailjet.com/docs/api) v1.

[![Latest Stable Version](https://poser.pugx.org/narno/mailjet-api/version)](https://packagist.org/packages/narno/mailjet-api) [![License](https://poser.pugx.org/narno/mailjet-api/license)](https://packagist.org/packages/narno/mailjet-api)

## Dependencies

* PHP 5.4+
* [zend-http](https://github.com/zendframework/zend-http)

> Note: There is also a version available for the [Zend Framework 1](https://github.com/Narno/Mailjet-API/tree/zf1).

## Installation

The recommended way is through [Composer](https://getcomposer.org).
```bash
composer require narno/mailjet-api
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

> Mailjet API documentation: http://www.mailjet.com/docs/api.

## License

Free software distributed under the terms of the [MIT license](http://opensource.org/licenses/MIT).

© [Arnaud Ligny](http://arnaudligny.fr)
