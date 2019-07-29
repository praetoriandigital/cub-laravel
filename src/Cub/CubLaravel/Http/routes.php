<?php

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
|
| This is where the Cub webhook is registered.
|
*/

Route::group(['domain' => config('cub.webhook_domain')], function () {
    Route::post(config('cub.webhook_url'), 'Cub\CubLaravel\Http\Controllers\CubWebhookController@receive');
});
