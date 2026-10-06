<?php

return [
    // Enable test mode for local/testing environments. When true,
    // views and controllers will show the test bank details from
    // the `test_bank` array and a visible banner.
    'test_mode' => env('TEST_MODE', false),

    'test_bank' => [
        'bank_name' => env('TEST_BANK_NAME', 'Bank Test'),
        'account_number' => env('TEST_ACCOUNT_NUMBER', 'TEST-000-000'),
        'account_holder' => env('TEST_ACCOUNT_HOLDER', 'Test Account'),
    ],
];
