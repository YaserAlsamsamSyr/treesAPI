<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['message' => "trees system"];
});

require __DIR__.'/auth.php';
