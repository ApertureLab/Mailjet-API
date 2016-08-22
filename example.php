<?php

// Composer autoloading
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    $loader = include __DIR__.'/vendor/autoload.php';
}

use Narno\Mailjet\Api as MailjetApi;
use Dotenv\Dotenv;

try {
    // load .env
    $dotenv = (new Dotenv(__DIR__))->load();
    // instantiate
    $api = New MailjetApi(getenv('MAILJET_API_KEY'), getenv('MAILJET_API_SECRET'));
    // fetches user's infos...
    $userInfos = $api->user->infos();
    if ($userInfos->status == 'OK') {
        // ...and displays
        print_r($userInfos->infos);
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
