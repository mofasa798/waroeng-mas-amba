<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $frontend = env('FRONTEND_URL', 'http://localhost:8080');
    return redirect($frontend . '/login');
});
