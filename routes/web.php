<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $frontend = env('FRONTEND_URL', 'http://waroeng-mas-amba.test:3000');
    return redirect($frontend . '/login');
});
