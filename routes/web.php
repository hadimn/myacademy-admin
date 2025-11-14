<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return response()->json([
        "status" => "success",
        "message" => "hello bro",
    ]);
});
