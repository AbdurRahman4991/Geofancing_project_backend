<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Company\CompanyControleer;


// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return view('landing.index');
});


Route::get('/email', function () {
    return view('emails.otp');
});



Route::get('/company/create', [
    CompanyControleer::class,
    'create'
])->name('company.create');


Route::post('/company', [
    CompanyControleer::class,
    'store'
])->name('company.store');