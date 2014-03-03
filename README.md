Description
-----------

*ZendService_Mailjet* provides a simple PHP library for the [Mailjet API](http://www.mailjet.com/docs/api).


Dependencies
------------

* PHP 5.2.4
* Zend Framework: http://www.framework.zend.com/downloads/latest#ZF1

Note: There is also a version available for the [Zend Framework 2](https://github.com/Narno/ZendService_Mailjet/tree/master).


How to use
----------

```php
<?php
require_once 'Zend/Service/Mailjet.php';

try {
    $mailjet = New Zend_Service_Mailjet('key', 'secret_key');
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
