# Cub Laravel

[![Built For Laravel][ico-built-for]][link-built-for]
[![Build Status][ico-travis]][link-travis]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

Laravel wrapper for [cub/cub][link-cub-php].

Use for authenticating your users with Cub. Keep your user data up-to-date with what is happening in Cub.

* [Compatibility](#compatibility)
* [Installation](#installation)
* [Usage](#usage)
* [Configuration](#configuration)

## Compatibility

Currently compatible with Laravel 5.*

## Installation

Via Composer

``` bash
$ composer require "cub/cub-laravel:~2.0"
```

After updating composer, add the CubLaravelServiceProvider to the `providers` array in `config/app.php`

``` php
Cub\CubLaravel\ServiceProvider::class,
```

Next make sure to add the Cub alias in the `aliases` array in the same `config/app.php`

``` php
'Cub' => Cub\CubLaravel\Facades\Cub::class,
'CubWidget' => Cub\CubLaravel\Facades\CubWidget::class,
```

Next add the `cub_id` field to your relevant Models with the following command:
```php
php artisan migrate --package="cub/cub-laravel"
```

Add `cub_id` to the relevant Models's `$fillable` array:
```php
    public $fillable = [
        ...
        'cub_id',
        ...
    ];
```

IMPORTANT: You will need to make sure to trigger the Cub webhook for the relevant Models.

## Usage

#### Logging in
Just pass the user's username and password to the Cub facade. You'll get a Login object that you use to access the User and the Cub token.
``` php
$login = Cub::login($username, $password);

// assuming the login was successful, this will be 
// an instance of your application's User model 
$user = $login->getUser();

// JWT token returned by Cub during login
$token = $login->getToken();
```

#### Getting a user by Cub `user` id
For convenience, you have the ability to get an instance of your application's User model when you possess a Cub `user` id.
 ```php
 // an instance of your application's User model
 $user = Cub::getUserById($cubId);
 ```
 
 #### Getting a user by JWT
 For convenience, you have the ability to get an instance of your application's User model when you possess a Cub JWT.
  ```php
  // an instance of your application's User model
  $user = Cub::getUserByJWT($jwt);
  ```
  
  Or if the Cub JWT was passed in on the request in the Authorization request header OR there is a `cubUserToken` cookie on the request OR the request was made with a query parameter with the key of `cub_token`. (The checking happens in the described order).
  ```php
  // an instance of your application's User model
  $user = Cub::getUserByJWT();
  ```
 
 #### Route filtering for JWT
 Filter routes based on the Cub JWT. The filter will check the request's Authorization header or it will check for the contents of the `cub_token` key in the request's query string.
 ```php
 Route::get('restricted', ['before' => 'cub-auth', function() {
    // after cub-auth the application user will be accessible as currentUser
    // an instance of your application's User model 
    $user = Cub::currentUser();
    return json_encode(['message' => "You're in, {$user->first_name}!"]);
 }]);
 ```
 
 #### Convenience helpers
 Sometimes you may just need to know if there is a valid Cub JWT on the incoming request. Use this:
 ```php
 $exists = Cub::validJWTExists();
 ```

 Get the current User and Token:
 ```php
 // an instance of your application's User model 
 $user = Cub::currentUser();

 // the current Cub JWT
 $token = Cub::currentToken();
 ```

 #### CubWidget
 Get all the widget elements you need with the `CubWidget` facade. Look to the Cub Widget [docs][link-cub-widget-docs] for further information about the widget.
 ```php
 CubWidget::headerScript();
 CubWidget::menu();
 CubWidget::app();
 // Footer script will be configured with your application's public key
 CubWidget::footerScript();
 ```

## Configuration

You're going to need to provide a few things in order to get this package working correctly for you.

If you don't need to change the default user model, you can get away with just setting the appropriate env variables. The necessary env variables are as follows.

```php
CUB_PUBLIC
CUB_SECRET
CUB_API_URL
CUB_WEBHOOK_URL

```

But if you need to change the user model, or if you prefer to set a lot of this in the config file you can do the following.

```php
php artisan config:publish cub/cub-laravel
```

Then you can update the config file as below. 

(DO NOT PUT YOUR SECRET KEY IN THE CONFIG FILE).

```php
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
    
    'public_key' => getEnv('CUB_PUBLIC'), // set to your application's public key
    
    /*
    |--------------------------------------------------------------------------
    | Cub Application Secret Key
    |--------------------------------------------------------------------------
    |
    | This is the Cub application secret key.
    |
    */
    
    'secret_key' => getEnv('CUB_SECRET'), // you should keep this as an environment variable

    /*
    |--------------------------------------------------------------------------
    | Cub Site Uid
    |--------------------------------------------------------------------------
    |
    | This is the Cub site uid.
    |
    */

    'site' => getEnv('CUB_SITE'), // this is needed for creation of cub objects
    
    /*
    |--------------------------------------------------------------------------
    | Cub Application API Url
    |--------------------------------------------------------------------------
    |
    | This is the Cub application api url.
    |
    */
    
    'api_url' => getEnv('CUB_API_URL'), // set to your application's api url

    /*
    |--------------------------------------------------------------------------
    | Cub Application Webhook Domain
    |--------------------------------------------------------------------------
    |
    | This is the Cub application webhook url domain.
    |
    */

    'webhook_domain' => getEnv('CUB_WEBHOOK_DOMAIN'), // this give you more control over the webhook
    
    /*
    |--------------------------------------------------------------------------
    | Cub Application Webhook Url
    |--------------------------------------------------------------------------
    |
    | This is the Cub application webhook url.
    |
    */
    
    'webhook_url' => 'webhooks/cub', // set this to your application's webhook url
    
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

        'user' => [

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

            // if you need to customize the processing of the
            // webhook data, create the transformer and update here
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
                // update the values of this array with the corresponding
                // fields on your model or comment them out
                // to ignore them
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

        'organization' => [

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

            // if you need to customize the processing of the
            // webhook data, create the transformer and update here
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
                // update the values of this array with the corresponding
                // fields on your model or comment them out
                // to ignore them
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

            /*
            |--------------------------------------------------------------------------
            | Application Organization Model Related Models
            |--------------------------------------------------------------------------
            |
            | This is where to set the Cub Organization related models.
            | Related models will be updated whenever the Organization
            | is updated.
            |
            */

            'relations' => [
                'state' => 'state_id',
                'country' => 'country_id',
            ],
        ],

        'member' => [

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

            // if you need to customize the processing of the
            // webhook data, create the transformer and update here
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
                // update the values of this array with the corresponding
                // fields on your model or comment them out
                // to ignore them
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

            /*
            |--------------------------------------------------------------------------
            | Application Member Model Related Models
            |--------------------------------------------------------------------------
            |
            | This is where to set the Cub Member related models.
            | Related models will be updated whenever the Member
            | is updated.
            |
            */

            'relations' => [
                'organization' => 'organization_id',
                'user' => 'user_id',
            ],
        ],

        'group' => [

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

            // if you need to customize the processing of the
            // webhook data, create the transformer and update here
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
                // update the values of this array with the corresponding
                // fields on your model or comment them out
                // to ignore them
                'id' => 'cub_id',
                'organization' => 'organization',
                'name' => 'name',
                'type' => 'type',
                'description' => 'description',
                'created' => 'created',
            ],

            /*
            |--------------------------------------------------------------------------
            | Application Group Model Related Models
            |--------------------------------------------------------------------------
            |
            | This is where to set the Cub Group related models.
            | Related models will be updated whenever the Group
            | is updated.
            |
            */

            'relations' => [
                'organization' => 'organization_id',
            ],
        ],

        'groupmember' => [

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

            // if you need to customize the processing of the
            // webhook data, create the transformer and update here
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
                // update the values of this array with the corresponding
                // fields on your model or comment them out
                // to ignore them
                'id' => 'cub_id',
                'group' => 'group',
                'member' => 'member',
                'is_admin' => 'is_admin',
                'created' => 'created',
            ],

            /*
            |--------------------------------------------------------------------------
            | Application GroupMember Model Related Models
            |--------------------------------------------------------------------------
            |
            | This is where to set the Cub GroupMember related models.
            | Related models will be updated whenever the GroupMember
            | is updated.
            |
            */

            'relations' => [
                'group' => 'group_id',
                'member' => 'member_id',
            ],
        ],

        'country' => [

            /*
            |--------------------------------------------------------------------------
            | Application Country Model
            |--------------------------------------------------------------------------
            |
            | This is the Country model which will be returned.
            |
            */

            'model' => 'App\Country',

            /*
            |--------------------------------------------------------------------------
            | Application Country Transformer
            |--------------------------------------------------------------------------
            |
            | This is the class that will handle the creating, updating,
            | and deleting of your Country Models.
            |
            */

            // if you need to customize the processing of the
            // webhook data, create the transformer and update here
            'transformer' => 'Cub\CubLaravel\Transformers\CubObjectTransformer',

            /*
            |--------------------------------------------------------------------------
            | Application Country Model Fields Map
            |--------------------------------------------------------------------------
            |
            | This is where the mapping of the Cub Country keys can
            | be mapped to the fields on your Country model.
            | i.e. 'cub_field' => 'application_field',
            |
            */

            'fields' => [
                // update the values of this array with the corresponding
                // fields on your model or comment them out
                // to ignore them
                'id' => 'cub_id',
                'name' => 'name',
                'code' => 'code',
                'code2' => 'code2',
                'code3' => 'code3',
            ],
        ],

        'state' => [

            /*
            |--------------------------------------------------------------------------
            | Application State Model
            |--------------------------------------------------------------------------
            |
            | This is the State model which will be returned.
            |
            */

            'model' => 'App\State',

            /*
            |--------------------------------------------------------------------------
            | Application State Transformer
            |--------------------------------------------------------------------------
            |
            | This is the class that will handle the creating, updating,
            | and deleting of your State Models.
            |
            */

            // if you need to customize the processing of the
            // webhook data, create the transformer and update here
            'transformer' => 'Cub\CubLaravel\Transformers\CubObjectTransformer',

            /*
            |--------------------------------------------------------------------------
            | Application State Model Fields Map
            |--------------------------------------------------------------------------
            |
            | This is where the mapping of the Cub State keys can
            | be mapped to the fields on your State model.
            | i.e. 'cub_field' => 'application_field',
            |
            */

            'fields' => [
                // update the values of this array with the corresponding
                // fields on your model or comment them out
                // to ignore them
                'id' => 'cub_id',
                'name' => 'name',
                'code' => 'code',
                'country' => 'country',
            ],

            /*
            |--------------------------------------------------------------------------
            | Application State Model Related Models
            |--------------------------------------------------------------------------
            |
            | This is where to set the Cub State related models.
            | Related models will be updated whenever the State
            | is updated.
            |
            */

            'relations' => [
                'country' => 'country_id',
            ],
        ],
    ],
    
);
```

## Development

A `docker-compose.yml` file is provided for running tests locally within an already defined docker container.

Run the following command to install all dependencies:
```bash
docker compose run php74 composer update
```

Run the following command to run tests:
```bash
docker compose run php74 vendor/bin/phpunit
```


[ico-version]: https://img.shields.io/packagist/v/cub/cub-laravel.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/praetoriandigital/cub-laravel/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/cub/cub-laravel.svg?style=flat-square
[ico-built-for]: https://img.shields.io/badge/built%20for-laravel-blue.svg

[link-packagist]: https://packagist.org/packages/cub/cub-laravel
[link-travis]: https://travis-ci.org/praetoriandigital/cub-laravel
[link-downloads]: https://packagist.org/packages/cub/cub-laravel
[link-built-for]: http://laravel.com
[link-cub-php]: https://packagist.org/packages/cub/cub
[link-cub-widget-docs]: https://github.com/praetoriandigital/cub-docs
