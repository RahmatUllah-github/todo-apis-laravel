<?php

use App\Services\JsonResponseService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/not-authenticated', function () {
    return JsonResponseService::unauthorizedErrorResponse('Your are not authenticated. Please login first.');
})->name('not-authenticated');
