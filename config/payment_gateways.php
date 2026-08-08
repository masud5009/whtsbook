<?php

return [
    'keywords' => [
        'paypal',
        'stripe',
        'paystack',
        'paytm',
        'flutterwave',
        'instamojo',
        'mollie',
        'razorpay',
        'mercadopago',
        'authorize.net',
        'yoco',
        'xendit',
        'toyyibpay',
        'iyzico',
        'phonepe',
        'paytabs',
        'midtrans',
        'perfect_money',
        'myfatoorah',
    ],

    // Schema used for:
    // - Blade rendering (fields/radios/title)
    // - Validation rules
    // - DB information JSON (info_fields)
    'forms' => [
        'stripe' => [
            'title' => 'Stripe',
            'meta'  => ['name' => 'Stripe', 'type' => 'automatic'],
            'text'  => 'Pay via your Credit account.',

            'rules' => [
                'key' => 'required|string',
                'secret' => 'required|string',
            ],

            'info_fields' => ['key', 'secret'],

            'fields' => [
                ['name' => 'key', 'label' => 'Stripe Key', 'ltr' => true],
                ['name' => 'secret', 'label' => 'Stripe Secret', 'ltr' => true],
            ],
        ],
        'paystack' => [
            'title' => 'Paystack',
            'meta'  => ['name' => 'Paystack', 'type' => 'automatic'],
            'text'  => 'Pay via your Paystack account.',

            'rules' => [
                'key' => 'required|string',
                'email' => 'required|email',
            ],

            'info_fields' => ['key', 'email'],

            'fields' => [
                ['name' => 'key', 'label' => 'Paystack Secret Key', 'ltr' => true],
            ],
        ],
        'flutterwave' => [
            'title' => 'Flutterwave',
            'meta'  => ['name' => 'Flutterwave', 'type' => 'automatic'],
            'text'  => 'Pay via your Flutterwave account.',

            'rules' => [
                'public_key' => 'required|string',
                'secret_key' => 'required|string',
            ],

            'info_fields' => ['public_key', 'secret_key'],

            'fields' => [
                ['name' => 'public_key', 'label' => 'Flutterwave Public Key', 'ltr' => true],
                ['name' => 'secret_key', 'label' => 'Flutterwave Secret Key', 'ltr' => true],
            ],
        ],
        'paypal' => [
            'title' => 'Paypal',
            'meta'  => ['name' => 'PayPal', 'type' => 'automatic'],
            'text'  => 'Pay via your PayPal account.',

            'rules' => [
                'client_id' => 'required|string',
                'client_secret' => 'required|string',
                'sandbox_check' => 'nullable|in:0,1',
            ],

            'info_fields' => ['client_id', 'client_secret', 'sandbox_check'],

            'radios' => [
                [
                    'name' => 'sandbox_check',
                    'label' => 'Paypal Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'client_id', 'label' => 'Paypal Client ID', 'ltr' => true],
                ['name' => 'client_secret', 'label' => 'Paypal Client Secret', 'ltr' => true],
            ],
        ],
        'instamojo' => [
            'title' => 'Instamojo',
            'meta'  => ['name' => 'Instamojo', 'type' => 'automatic'],
            'text'  => 'Pay via your Instamojo account.',

            'rules' => [
                'key' => 'required|string',
                'token' => 'required|string',
                'sandbox_check' => 'nullable|in:0,1',
            ],

            'info_fields' => ['key', 'token', 'sandbox_check'],

            'radios' => [
                [
                    'name' => 'sandbox_check',
                    'label' => 'Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'key', 'label' => 'Instamojo API Key', 'ltr' => true],
                ['name' => 'token', 'label' => 'Instamojo Auth Token', 'ltr' => true],
            ],
        ],
        'iyzico' => [
            'title' => 'Iyzico',
            'meta'  => ['name' => 'Iyzico', 'type' => 'automatic'],
            'text'  => 'Pay via your Iyzico account.',

            'rules' => [
                'api_key' => 'required|string',
                'secret_key' => 'required|string',
            ],

            'info_fields' => ['api_key', 'secret_key', 'sandbox_status'],

            'radios' => [
                [
                    'name' => 'sandbox_status',
                    'label' => 'Iyzico Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'api_key', 'label' => 'Api Key', 'ltr' => true],
                ['name' => 'secret_key', 'label' => 'Secret Key', 'ltr' => true],
            ],
        ],
        'razorpay' => [
            'title' => 'Razorpay',
            'meta'  => ['name' => 'Razorpay', 'type' => 'automatic'],
            'text'  => 'Pay via your Razorpay account.',

            'rules' => [
                'key' => 'required|string',
                'secret' => 'required|string',
            ],

            'info_fields' => ['key', 'secret'],

            'fields' => [
                ['name' => 'key', 'label' => 'Razorpay Key', 'ltr' => true],
                ['name' => 'secret', 'label' => 'Razorpay Secret', 'ltr' => true],
            ],
        ],
        'mercadopago' => [
            'title' => 'Mercado Pago',
            'meta'  => ['name' => 'Mercado Pago', 'type' => 'automatic'],
            'text'  => 'Pay via your Mercado Pago account.',

            'rules' => [
                'token' => 'required|string',
                'sandbox_check' => 'nullable|in:0,1',
            ],

            'info_fields' => ['token', 'sandbox_check'],

            'radios' => [
                [
                    'name' => 'sandbox_check',
                    'label' => 'Mercado Pago Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'token', 'label' => 'Mercadopago Token', 'ltr' => true],
            ],
        ],
        'myfatoorah' => [
            'title' => 'MyFatoorah Payment',
            'meta'  => ['name' => 'MyFatoorah', 'type' => 'automatic'],
            'text'  => 'Pay via your MyFatoorah account.',

            'rules' => [
                'token' => 'required',
            ],

            'info_fields' => ['token', 'sandbox_status'],
            'radios' => [
                [
                    'name' => 'sandbox_status',
                    'label' => 'MyFatoorah Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'token', 'label' => 'Token', 'ltr' => true],
            ],
        ],
        'perfect_money' => [
            'title' => 'Perfect Money',
            'meta'  => ['name' => 'Perfect Money', 'type' => 'automatic'],
            'text'  => 'Pay via your Perfect Money account.',

            'rules' => [
                'perfect_money_wallet_id' => 'required|string',
            ],

            'info_fields' => ['perfect_money_wallet_id'],


            'fields' => [
                ['name' => 'perfect_money_wallet_id', 'label' => 'Perfect Money Wallet Id', 'ltr' => true],
            ],
        ],
        'yoco' => [
            'title' => 'Yoco',
            'meta'  => ['name' => 'Yoco', 'type' => 'automatic'],
            'text'  => 'Pay via your Yoco account.',

            'rules' => [
                'secret_key' => 'required|string',
            ],

            'info_fields' => ['secret_key'],

            'fields' => [
                ['name' => 'secret_key', 'label' => 'Secret Key', 'ltr' => true],
            ],
        ],
        'xendit' => [
            'title' => 'Xendit',
            'meta'  => ['name' => 'Xendit', 'type' => 'automatic'],
            'text'  => 'Pay via your xendit account.',

            'rules' => [
                'secret_key' => 'required|string',
            ],

            'info_fields' => ['secret_key'],

            'fields' => [
                ['name' => 'secret_key', 'label' => 'Secret Key', 'ltr' => true],
            ],
        ],
        'phonepe' => [
            'title' => 'PhonePe',
            'meta'  => ['name' => 'PhonePe', 'type' => 'automatic'],
            'text'  => 'Pay via your PhonePe account.',

            'rules' => [
                'merchant_id' => 'required|string',
                'salt_key' => 'required|string',
                'salt_index' => 'required|string',
            ],

            'info_fields' => ['merchant_id', 'salt_key', 'salt_index', 'sandbox_status'],

            'radios' => [
                [
                    'name' => 'sandbox_status',
                    'label' => 'PhonePe Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'merchant_id', 'label' => 'Merchant Id', 'ltr' => true],
                ['name' => 'salt_key', 'label' => 'Salt Key', 'ltr' => true],
                ['name' => 'salt_index', 'label' => 'Salt Index', 'ltr' => true],
            ],
        ],
        'paytabs' => [
            'title' => 'Paytabs',
            'meta'  => ['name' => 'Paytabs', 'type' => 'automatic'],
            'text'  => 'Pay via your Paytabs account.',

            'rules' => [
                'server_key' => 'required|string',
                'profile_id' => 'required|string',
                'country' => 'required|string',
                'api_endpoint' => 'required|string',
            ],

            'info_fields' => ['server_key', 'profile_id', 'country', 'api_endpoint'],

            'fields' => [
                ['name' => 'server_key', 'label' => 'Server Key', 'ltr' => true],
                ['name' => 'profile_id', 'label' => 'Profile Id', 'ltr' => true],
                ['name' => 'country', 'label' => 'Country', 'ltr' => true],
                ['name' => 'api_endpoint', 'label' => 'API Endpoint', 'ltr' => true],
            ],
        ],
        'authorize.net' => [
            'title' => 'Authorize.Net',
            'meta'  => ['name' => 'Authorize.net', 'type' => 'automatic'],
            'text'  => 'Pay via your Authorize.net account.',

            'rules' => [
                'login_id' => 'required|string',
                'transaction_key' => 'required|string',
                'public_key' => 'nullable|string',
                'sandbox_check' => 'nullable|in:0,1',
            ],

            'info_fields' => ['login_id', 'transaction_key', 'public_key', 'sandbox_check'],

            'radios' => [
                [
                    'name' => 'sandbox_check',
                    'label' => 'Authorize.Net Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'login_id', 'label' => 'API Login ID', 'ltr' => true],
                ['name' => 'transaction_key', 'label' => 'Transaction Key', 'ltr' => true],
                ['name' => 'public_key', 'label' => 'Public Client Key', 'ltr' => true],
            ],
        ],
        'midtrans' => [
            'title' => 'Midtrans',
            'meta'  => ['name' => 'Midtrans', 'type' => 'automatic'],
            'text'  => 'Pay via your Midtrans account.',

            'rules' => [
                'server_key' => 'required|string',
            ],

            'info_fields' => ['server_key', 'is_production'],

            'radios' => [
                [
                    'name' => 'is_production',
                    'label' => 'PhonePe Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'server_key', 'label' => 'Server Key', 'ltr' => true],
            ],
        ],
        'toyyibpay' => [
            'title' => 'Toyyibpay',
            'meta'  => ['name' => 'Toyyibpay', 'type' => 'automatic'],
            'text'  => 'Pay via your Toyyibpay account.',

            'rules' => [
                'secret_key' => 'required|string',
                'category_code' => 'required|string',
            ],

            'info_fields' => ['secret_key', 'category_code', 'sandbox_status'],

            'radios' => [
                [
                    'name' => 'sandbox_status',
                    'label' => 'Toyyibpay Test Mode',
                    'options' => [
                        ['value' => 1, 'text' => 'Active'],
                        ['value' => 0, 'text' => 'Deactive'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'secret_key', 'label' => 'Secret Key', 'ltr' => true],
                ['name' => 'category_code', 'label' => 'Category Code', 'ltr' => true],
            ],
        ],
        'mollie' => [
            'title' => 'Mollie Payment',
            'meta'  => ['name' => 'Mollie', 'type' => 'automatic'],
            'text'  => 'Pay via your Mollie Payment account.',

            'rules' => [
                'key' => 'required|string',
            ],

            'info_fields' => ['key'],

            'fields' => [
                ['name' => 'key', 'label' => 'Mollie Payment Key', 'ltr' => true],
            ],
        ],
        'paytm' => [
            'title' => 'Paytm',
            'meta'  => ['name' => 'Paytm', 'type' => 'automatic'],
            'text'  => 'Pay via your Paytm account.',


            'rules' => [
                'environment' => 'required|in:local,production',
                'merchant' => 'required|string',
                'secret' => 'required|string',
                'website' => 'required|string',
                'industry' => 'required|string',
            ],

            'info_fields' => ['environment', 'merchant', 'secret', 'website', 'industry'],

            'radios' => [
                [
                    'name' => 'environment',
                    'label' => 'Paytm Environment',
                    'options' => [
                        ['value' => 'local', 'text' => 'Local'],
                        ['value' => 'production', 'text' => 'Production'],
                    ],
                ],
            ],

            'fields' => [
                ['name' => 'secret', 'label' => 'Paytm Merchant Key', 'ltr' => true],
                ['name' => 'merchant', 'label' => 'Paytm Merchant mid', 'ltr' => true],
                ['name' => 'website', 'label' => 'Paytm Merchant website'],
                ['name' => 'industry', 'label' => 'Industry type id'],
            ],
        ],
    ],
];
