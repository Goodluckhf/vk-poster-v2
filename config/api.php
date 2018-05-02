<?php

return [
    'like_token'   => env('API_LIKE_TOKEN', ''),
    'support_mail' => env('API_SUPPORT_MAIL', 'goodluckhf@yandex.ru'),
    
    'google'       => [
    	'catcha_secret' => env('GOOGLE_CAPTCHA_SECRET', ''),
    	'catcha_url'    => env('CAPTCHA_URL', '')
    ]
];