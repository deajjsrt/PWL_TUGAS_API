<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BagController;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/bag', function () {
    return view('pages.plp');
})->name('plp');

Route::get('/bag/{i}', function () {
    return view('pages.pdp');
})->name('pdp');
