# Cub Laravel

[![Built For Laravel][ico-built-for]][link-built-for]
[![Build Status][ico-travis]][link-travis]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

Laravel wrapper for [ivelum/cub-php][link-cub-php].

Use for authenticating your users with Cub. Keep your user data up-to-date with what is happening in Cub.

* [Compatibility](#compatibility)
* [Installation](#installation)
* [Usage](#usage)
* [Configuration](#configuration)

## Compatibility

Currently only compatible with Laravel 4.2.*

## Installation

Via Composer

``` bash
$ composer require "cub/cub-laravel:~1.0"
```

After updating composer, add the CubLaravelServiceProvider to the `providers` array in `config/app.php`

``` php
Cub\CubLaravel\Providers\CubLaravelServiceProvider::class,
```

Next make sure to add the Cub alias in the `aliases` array in the same `config/app.php`

``` php
'Cub' => Cub\CubLaravel\Facades\Cub::class,
```

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
  
  Or if the Cub JWT was passed in on the request in the Authorization request header or with a query parameter with the key of `cub_token`.
  ```php
  // an instance of your application's User model
  $user = Cub::getUserByJWT();
  ```
 
 #### Route filtering for JWT
 Filter routes based on the Cub JWT. The filter will check the request's Authorization header or it will check for the contents of the `cub_token` key in the request's query string.
 ```php
 Route::get('restricted', ['before' => 'cub-auth', function() {
    return json_encode(['message' => 'You're in!']);
 }]);
 ```
 
 #### Convenience helper
 Sometimes you may just need to know if there is a valid Cub JWT on the incoming request. Use this:
 ```php
 $exists = Cub::validJWTExists();
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
php artisan config:publish praetoriandigital/cub-laravel
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
    
    'public_key' => getEnv('CUB_PUBLIC'), // set to you application's public key
    
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
    | Cub Application API Url
    |--------------------------------------------------------------------------
    |
    | This is the Cub application api url.
    |
    */
    
    'api_url' => getEnv('CUB_API_URL'), // set to your application's api url
    
    /*
    |--------------------------------------------------------------------------
    | Cub Application Webhook Url
    |--------------------------------------------------------------------------
    |
    | This is the Cub application webhook url.
    |
    */
    
    'webhook_url' => getEnv('CUB_WEBHOOK_URL'), // set this to your application's webhook url
    
    /*
    |--------------------------------------------------------------------------
    | Application User Model
    |--------------------------------------------------------------------------
    |
    | This is the user model which will be returned.
    |
    */
    
    'user' => 'App\User', // update to the fully qualified namespace of your user model
    
    /*
    |--------------------------------------------------------------------------
    | Application User Model Fields Map
    |--------------------------------------------------------------------------
    |
    | This is where the mapping of the Cub User keys can
    | be mapped to the fields on your User model.
    |
    */
    
    // update the values of this array with the corresponding
    // fields on your User model
    // i.e. 'email' => 'user_email'
    'fields' => [
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email',
        'username' => 'username',
    ],
    
);
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
[link-cub-php]: https://github.com/ivelum/cub-php
