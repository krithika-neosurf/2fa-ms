<?php

return [
    // Default code digit length (values: 4 or 6)
    'code_length' => env('2FA_CODE_LENGTH', 4),
    
    // Verification code validity in mins
    'code_validity' => env('2FA_CODE_VALIDITY', 5),

    // Maximum retries in generating and sending code before failing
    'send_code_retries' => env('2FA_SEND_CODE_RETRIES', 5),

    // Token expiry in hours
    'token_expiry' => env('2FA_TOKEN_EXPIRY', 24),

    'origins' => [
        'pay',
        'neopass',
    ],
];