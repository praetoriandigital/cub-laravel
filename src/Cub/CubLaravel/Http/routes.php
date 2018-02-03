<?php

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
|
| This is where the Cub webhook is registered.
|
*/

Route::group(['domain' => Config::get('cub::config.webhook_domain')], function () {
    Route::post(Config::get('cub::config.webhook_url'), 'Cub\CubLaravel\Http\Controllers\CubWebhookController@receive');
});
