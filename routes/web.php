<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::any('/', function(){
    echo 'API Telekonsultasi v2';
});

Route::get('/verification/account/{code}/{token}', [AuthController::class, 'VerificationAccount']);

Route::group(['middleware' => 'access'], function(){
    Route::group(['middleware' => 'access:admin'], function(){
        Route::group(['prefix' => 'admin'], function(){
            Route::any('/', function(){
                return view('admin');
            });
    
            Route::get('{any}', function(){
                return view('admin');
            })->where('any', '^(?!api).*$');
        });
    });
    
    Route::group(['middleware' => 'access:doctor'], function(){
       Route::group(['prefix' => 'report'], function(){
           Route::get('print', [ReportController::class, 'Print']);
       }); 
    });
});

Route::get('{any}', function () {
    return view('welcome');
})->where('any', '^(?!api).*$');
