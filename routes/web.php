<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BookController;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/book', function () {
    return view('pages.plp');
})->name('plp');

Route::get('/book/{i}', function () {
    return view('pages.pdp');
})->name('pdp');
