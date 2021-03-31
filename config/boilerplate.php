<?php

return [
    // these options are related to the sign-up procedure
    'sign_up' => [
        // this option must be set to true if you want to release a token
        // when your user successfully terminates the sign-in procedure
        'release_token' => env('SIGN_UP_RELEASE_TOKEN', false),

        // here you can specify some validation rules for your sign-in request
        'validation_rules' => [
            'name' => 'required',
            'email' => 'required|email|unique',
            'password' => 'required',
        ],
    ],

    // these options are related to the login procedure
    'login' => [
        // here you can specify some validation rules for your login request
        'validation_rules' => [
            'email' => 'required|email',
            'password' => 'required',
        ],
    ],

    // these options are related to the password recovery procedure
    'forgot_password' => [
        // here you can specify some validation rules for your password recovery procedure
        'validation_rules' => [
            'email' => 'required|email',
        ],
    ],

    // these options are related to the password recovery procedure
    'reset_password' => [
        // this option must be set to true if you want to release a token
        // when your user successfully terminates the password reset procedure
        'release_token' => env('PASSWORD_RESET_RELEASE_TOKEN', false),

        // here you can specify some validation rules for your password recovery procedure
        'validation_rules' => [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ],
    ],

    'investor_save' => [
        'validation_rules' => [
            'name' => 'required',
        ],
    ],

    'promoters_save' => [
        'validation_rules' => [
            'name' => 'required',
        ],
    ],

    'unit_save' => [
        'validation_rules' => [
            'property_id' => 'required',
            'investor_id' => 'required',
            'id_orange' => 'required',
            'floor' => 'required',
            'in_pool' => 'required',
        ],
    ],

    'unit_contract' => [
        'validation_rules' => [
            'contract_id' => 'required',
            'file' => 'required_if:contract_id,0|mimetypes:image/jpeg,image/png,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword',
            'date_begin' => 'required|date_format:"Y-m-d"|before:date_expires',
            'date_begin_page' => 'required',
            'date_expires' => 'required|date_format:"Y-m-d"|after:date_begin',
            'date_expires_page' => 'required',
            'amount' => 'required',
            'amount_page' => 'required',
            'lessee' => 'required',
            'lessee_page' => 'required',
        ],
    ],

    'unit_insurance' => [
        'validation_rules' => [
            'insurance_id' => 'required',
            'file' => 'required_if:policy_insurance_id,0|mimetypes:image/jpeg,image/png,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword',
            'date_begin' => 'required|date_format:"Y-m-d"|before:date_expires',
            'date_begin_page' => 'required',
            'date_expires' => 'required|date_format:"Y-m-d"|after:date_begin',
            'date_expires_page' => 'required',
            'amount' => 'required',
            'amount_page' => 'required',
            'lessee' => 'required',
            'lessee_page' => 'required',
        ],
    ],

    'income_save' => [
        'validation_rules' => [
            'category_id' => 'required',
            'incomeable_id' => 'required',
            'incomeable_type' => 'required',
            'concept' => 'required',
            'amount' => 'required',
            'date' => 'required|date_format:"Y-m-d"',
        ],
    ],

    'expense_save' => [
        'validation_rules' => [
            'category_id' => 'required',
            'expendable_id' => 'required',
            'expendable_type' => 'required',
            'concept' => 'required',
            'amount' => 'required',
            'date' => 'required|date_format:"Y-m-d"',
        ],
    ],

    'user_save' => [
        'validation_rules' => [
            'name' => 'required|max:255',
            'username' => 'unique:users',
            'password' => 'required|min:6',
            'email' => 'required|unique:users',
            'role' => 'required',
        ],
    ],

    'attachment_save' => [
        'validation_rules' => [
            'investor_id' => 'required',
            'contract_id' => 'required',
            'amount' => 'required',
            'date_init' => 'required|date_format:"Y-m-d"|before:date_expire',
            'date_expire' => 'required|date_format:"Y-m-d"|after:date_init',
        ],
    ],

    'promissory_save' => [
        'validation_rules' => [
            'folio' => 'required|unique:promissorys',
        ],
    ],

    'promissory_cancel' => [
        'validation_rules' => [
            'reason' => 'required',
        ],
    ],

    'attachment_renew' => [
        'validation_rules' => [
            'date_init' => 'required|date_format:"Y-m-d"|before:date_expire',
            'date_expire' => 'required|date_format:"Y-m-d"|after:date_init',
        ],
    ],

    'withdraw_save' => [
        'validation_rules' => [
            'investor_id' => 'required',
            'contract_id' => 'required',
            'amount' => 'required',
            'date_retirement' => 'required|date_format:"Y-m-d"',
        ],
    ],

    'affected_attachment' => [
        'validation_rules' => [
            'contract_id' => 'required',
            'amount' => 'required',
            'date_retirement' => 'required|date_format:"Y-m-d"',
        ],
    ],

    'withdrawal_cancel' => [
        'validation_rules' => [
            'reason' => 'required',
        ],
    ],

    'monthly_cut_save' => [
        'validation_rules' => [
            'year' => 'required',
            'month' => 'required',
            'cut_day' => 'required',
        ],
    ],

    'send_statements' => [
        'validation_rules' => [
            'investors' => 'required',
            'message' => 'required',
            'subject' => 'required',
        ],
    ],

    'contract_save' => [
        'validation_rules' => [
            'folio' => 'required|unique:contracts,folio,NULL,id,deleted_at,NULL',
            'date_init' => 'required|date_format:"Y-m-d"',
            'interest_rate' => 'required',
            'cut_day' => 'required',
            'frequency_id' => 'required|exists:frequencies,id',
            'contract_type_id' => 'required|exists:contract_types,id',
            'investor_id' => 'required|exists:investors,id',
            'mixed_rate' => 'required_if:contract_type_id,2',
        ],
    ],
];
