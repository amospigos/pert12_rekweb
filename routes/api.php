<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdukController;

// Endpoint untuk mengecek kesiapan RESTful API
Route::get('/cek-api', function () {
    return response()->json([
        'success' => true,
        'status'  => 'ONLINE',
        'message' => 'RESTful API Project pert11 aktif dan siap digunakan.'
    ]);
});

// Endpoint CRUD yang diproteksi Bearer Token
Route::middleware('api.token')->group(function () {
    Route::apiResource('/produks', ProdukController::class);
});