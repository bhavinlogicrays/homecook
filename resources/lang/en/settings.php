<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings Language Lines
    |--------------------------------------------------------------------------
    |
    |
    */
    'system_status'=> 'System status',
    'how_to_fix_this'=> 'How to fix this',
    'setup_progress'=>'Setup Progress',
    'configuration_test'=>"This is email configuration test",
    'default_admin_email' => 'Check for default admin email.',
    'default_admin_email_ok' => 'Your email is changed correctly. Thanks',
    'using_default_admin_solution' => 'You are using default email and this is security risk. Please read in the docummentation how to set up your own email.',
    'smtp'=>'Mail sending',
    'smtp_not_ok'=>'We where not able to send test email. Please make sure that you have entered correct SMTP credentials. Please note that you can use both SSL and no SSL ports. When using SSL, makse sure MAIL_DRIVER is not smtp, but sendmail, as in the docs.',
    'paddle'=>'Paddle',
    'paddle_error'=>"Look like you haven't setup your paddle. Paddle.com is used for subscriptions and payments. Please follow the docs and add your paddle credentials in settings",
    'stripe'=>'Stripe',
    'stripe_error'=>"Look like you haven't setup your stripe. Stripe is used for subscriptions and payments. Please follow the docs and add your stripe credentials in settings",
    'site_run_into_smtp_error'=>"Unfortunertly system run into smtp error. If yoou are admin, plese login and check system status."
];
