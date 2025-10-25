<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('books');
});

Route::get('/top-author', function () {
    return view('top-author');
});

Route::get('/add-rating', function () {
    return view('add-rating');
});
