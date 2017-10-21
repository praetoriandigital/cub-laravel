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
    | Cub Object Mapping
    |--------------------------------------------------------------------------
    |
    | This is where you will configure how Cub objects map
    | to your application Models
    |
    */

    'maps' => [

        /*
        |--------------------------------------------------------------------------
        | User Mapping Information
        |--------------------------------------------------------------------------
        */

        'cub_user' => [

            /*
            |--------------------------------------------------------------------------
            | Application User Model
            |--------------------------------------------------------------------------
            |
            | This is the user model which will be returned.
            |
            */

            'model' => 'App\User',

            /*
            |--------------------------------------------------------------------------
            | Application User Transformer
            |--------------------------------------------------------------------------
            |
            | This is the class that will handle the creating, updating,
            | and deleting of your User Models.
            |
            */

            'transformer' => 'Cub\CubLaravel\Transformers\CubObjectTransformer',

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
        ],

        'cub_organization' => [

            /*
            |--------------------------------------------------------------------------
            | Application Organization Model
            |--------------------------------------------------------------------------
            |
            | This is the organization model which will be returned.
            |
            */

            'model' => 'App\Organization',

            /*
            |--------------------------------------------------------------------------
            | Application Organization Transformer
            |--------------------------------------------------------------------------
            |
            | This is the class that will handle the creating, updating,
            | and deleting of your Organization Models.
            |
            */

            'transformer' => 'Cub\CubLaravel\Transformers\CubObjectTransformer',

            /*
            |--------------------------------------------------------------------------
            | Application Organization Model Fields Map
            |--------------------------------------------------------------------------
            |
            | This is where the mapping of the Cub Organization keys can
            | be mapped to the fields on your Organization model.
            | i.e. 'cub_field' => 'application_field',
            |
            */

            'fields' => [
                'id' => 'cub_id',
                'name' => 'name',
                'employees' => 'employees',
                'tags' => 'tags',
                'country' => 'country',
                'state' => 'state',
                'city' => 'city',
                'county' => 'county',
                'postal_code' => 'postal_code',
                'address' => 'address',
                'phone' => 'phone',
                'hr_phone' => 'hr_phone',
                'fax' => 'fax',
                'website' => 'website',
                'created' => 'created',
                'logo' => 'logo',
            ],
        ],

        'cub_member' => [

            /*
            |--------------------------------------------------------------------------
            | Application Member Model
            |--------------------------------------------------------------------------
            |
            | This is the member model which will be returned.
            |
            */

            'model' => 'App\Member',

            /*
            |--------------------------------------------------------------------------
            | Application Member Transformer
            |--------------------------------------------------------------------------
            |
            | This is the class that will handle the creating, updating,
            | and deleting of your Member Models.
            |
            */

            'transformer' => 'Cub\CubLaravel\Transformers\CubObjectTransformer',

            /*
            |--------------------------------------------------------------------------
            | Application Member Model Fields Map
            |--------------------------------------------------------------------------
            |
            | This is where the mapping of the Cub Member keys can
            | be mapped to the fields on your Member model.
            | i.e. 'cub_field' => 'application_field',
            |
            */

            'fields' => [
                'id' => 'cub_id',
                'organization' => 'organization',
                'user' => 'user',
                'invitation' => 'invitation',
                'personal_id' => 'personal_id',
                'post_id' => 'post_id',
                'notes' => 'notes',
                'is_active' => 'is_active',
                'is_admin' => 'is_admin',
                'positions' => 'positions',
                'group_membership' => 'group_membership',
                'created' => 'created',
            ],
        ],

        'cub_group' => [

            /*
            |--------------------------------------------------------------------------
            | Application Group Model
            |--------------------------------------------------------------------------
            |
            | This is the group model which will be returned.
            |
            */

            'model' => 'App\Group',

            /*
            |--------------------------------------------------------------------------
            | Application Group Transformer
            |--------------------------------------------------------------------------
            |
            | This is the class that will handle the creating, updating,
            | and deleting of your Group Models.
            |
            */

            'transformer' => 'Cub\CubLaravel\Transformers\CubObjectTransformer',

            /*
            |--------------------------------------------------------------------------
            | Application Group Model Fields Map
            |--------------------------------------------------------------------------
            |
            | This is where the mapping of the Cub Group keys can
            | be mapped to the fields on your Group model.
            | i.e. 'cub_field' => 'application_field',
            |
            */

            'fields' => [
                'id' => 'cub_id',
                'organization' => 'organization',
                'name' => 'name',
                'type' => 'type',
                'description' => 'description',
                'created' => 'created',
            ],
        ],

        'cub_groupmember' => [

            /*
            |--------------------------------------------------------------------------
            | Application GroupMember Model
            |--------------------------------------------------------------------------
            |
            | This is the GroupMember model which will be returned.
            |
            */

            'model' => 'App\GroupMember',

            /*
            |--------------------------------------------------------------------------
            | Application GroupMember Transformer
            |--------------------------------------------------------------------------
            |
            | This is the class that will handle the creating, updating,
            | and deleting of your GroupMember Models.
            |
            */

            'transformer' => 'Cub\CubLaravel\Transformers\CubObjectTransformer',

            /*
            |--------------------------------------------------------------------------
            | Application GroupMember Model Fields Map
            |--------------------------------------------------------------------------
            |
            | This is where the mapping of the Cub GroupMember keys can
            | be mapped to the fields on your GroupMember model.
            | i.e. 'cub_field' => 'application_field',
            |
            */

            'fields' => [
                'id' => 'cub_id',
                'group' => 'group',
                'member' => 'member',
                'is_admin' => 'is_admin',
                'created' => 'created',
            ],
        ],
    ],

);
