<?php

return [
    'queue_connection' => env(
        'OTP_QUEUE_CONNECTION',
        'redis'
    ),

    'queue' => env(
        'OTP_QUEUE',
        'otp'
    ),
];