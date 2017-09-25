<?php

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
|
| This is where the Cub webhook is registered.
|
*/

Route::post(Config::get('cub.webhook_url'), 'Praetoriandigital\CubLaravel\Controllers\CubWebhookController@receive');
