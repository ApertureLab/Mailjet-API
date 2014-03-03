Description
-----------

*ZendService_Mailjet* provides a simple PHP library for the [Mailjet API](http://www.mailjet.com/docs/api).


Dependencies
------------

* PHP 5.3+
* [Zend\Http](https://github.com/zendframework/Component_ZendHttp) component ([Zend Framework 2](https://github.com/zendframework/zf2))

Note: There is also a version available for the [Zend Framework 1](https://github.com/Narno/ZendService_Mailjet/tree/zf1).


Installation
------------

You can install this component using [Composer](https://getcomposer.org/) with following commands:

    curl -s https://getcomposer.org/installer | php
    php composer.phar install


Usage
-----

```php
<?php
use ZendService\Mailjet\Mailjet as Mailjet;

try {
    $mailjet = New Mailjet('key', 'secret_key');
    // fetches user's infos...
    $userInfos = $mailjet->user->infos();
    if ($userInfos->status == 'OK') {
        // ...and displays username
        echo $userInfos->infos->username;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
```

Mailjet API documentation: http://www.mailjet.com/docs/api


License
----------

*ZendService_Mailjet* is released under the terms of the [MIT license](http://opensource.org/licenses/MIT).

Copyright (c) 2012-2014 Arnaud Ligny
