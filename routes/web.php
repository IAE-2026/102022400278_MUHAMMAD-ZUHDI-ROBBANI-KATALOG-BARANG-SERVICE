<?php

use Illuminate\Support\Facades\Route;

Route::view('/graphql-playground', 'graphql-playground');

Route::get('/', function () {
    return view('welcome');
});
