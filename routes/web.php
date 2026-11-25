<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('produk_crud');
});