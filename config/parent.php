<?php

return [

    'default_password' => env('PARENT_DEFAULT_PASSWORD', 'Parent123'),

    'portal_name' => env('PARENT_PORTAL_NAME', 'Parent Portal'),

    'login_attempts' => 5,

    'lockout_minutes' => 10,

];
