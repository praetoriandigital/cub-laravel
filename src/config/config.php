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

  'webhook_url' => getEnv('CUB_WEBHOOK_URL'),

  /*
  |--------------------------------------------------------------------------
  | Application User Model
  |--------------------------------------------------------------------------
  |
  | This is the user model which will be returned.
  |
  */

  'user' => 'App\User',

);
