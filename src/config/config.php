<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Cub Application Public Key
    |--------------------------------------------------------------------------
    |
    | This is the Cub application public key.
    |
    */

    'public_key' => getEnv('CUB_PUBLIC'),

    /*
    |--------------------------------------------------------------------------
    | Cub Application Secret Key
    |--------------------------------------------------------------------------
    |
    | This is the Cub application secret key.
    |
    */

    'secret_key' => getEnv('CUB_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Cub Application API Url
    |--------------------------------------------------------------------------
    |
    | This is the Cub application api url.
    |
    */

    'api_url' => getEnv('CUB_API_URL'),

    /*
    |--------------------------------------------------------------------------
    | Cub Application Webhook Url
    |--------------------------------------------------------------------------
    |
    | This is the Cub application webhook url.
    |
    */

    'webhook_url' => 'webhooks/cub',

    /*
    |--------------------------------------------------------------------------
    | Application User Model
    |--------------------------------------------------------------------------
    |
    | This is the user model which will be returned.
    |
    */

    'user' => 'App\User',

    /*
    |--------------------------------------------------------------------------
    | Application User Model Fields Map
    |--------------------------------------------------------------------------
    |
    | This is where the mapping of the Cub User keys can
    | be mapped to the fields on your User model.
    | i.e. 'cub_field' => 'application_field',
    |
    */

    'fields' => [
        // more will come later as necessary
        'birth_date' => 'birth_date',
        'date_joined' => 'date_joined',
        'email' => 'email',
        'email_confirmed' => 'email_confirmed',
        'first_name' => 'first_name',
        'gender' => 'gender',
        'id' => 'cub_id',
        'invalid_email' => 'invalid_email',
        'invitation_last_sent_on' => 'invitation_last_sent_on',
        'invitation_sent_count' => 'invitation_sent_count',
        'last_login' => 'last_login',
        'last_name' => 'last_name',
        'middle_name' => 'middle_name',
        'original_username' => 'original_username',
        'password_change_required' => 'password_change_required',
        'photo_large' => 'photo_large',
        'photo_small' => 'photo_small',
        'purchasing_role_buy_for_organization' => 'purchasing_role_buy_for_organization',
        'purchasing_role_buy_for_self_only' => 'purchasing_role_buy_for_self_only',
        'purchasing_role_recommend' => 'purchasing_role_recommend',
        'purchasing_role_specify_for_organization' => 'purchasing_role_specify_for_organization',
        'registration_site' => 'registration_site',
        'retired' => 'retired',
        'token' => 'token',
        'username' => 'username',
    ],

);
