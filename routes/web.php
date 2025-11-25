<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return response()->json(['message' => 'Please login.']);
})->name('login');

Route::get('/',function (){
    return view('welcome');
});
