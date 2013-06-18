Description
-----------

_ZendService_Mailjet_ provides a simple PHP library for the Mailjet API.

*Work in progress...*

Mailjet API documentation: https://fr.mailjet.com/docs/api


How to use
----------

```
<?php
use ZendService\Mailjet\Mailjet as Mailjet;

try {
    $mj = New Mailjet('key', 'secret_key');
    // user infos
    $userInfos = $mj->userGetInfos();
    echo $userInfos->firstname;
} catch (Exception $e) {
    echo $e->getMessage();
}
```


License
----------

_ZendService_Mailjet_ is released under the terms of the [MIT license](http://opensource.org/licenses/MIT).

Copyright (c) 2012 Arnaud Ligny

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
