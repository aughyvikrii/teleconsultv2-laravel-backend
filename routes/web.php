<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReportController;
use App\Http\Controllers\Controller;

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
Route::any('/', [Controller::class, 'landing']);

Route::any('redirect', [Controller::class, 'redirect']);

Route::view('/auth', 'patient');

Route::get('/verification/account/{code}/{token}', [AuthController::class, 'VerificationAccount']);

Route::group(['middleware' => 'access'], function(){
    
    Route::group(['middleware' => 'access:doctor'], function(){
       Route::group(['prefix' => 'report'], function(){
           Route::get('print', [ReportController::class, 'Print']);
       }); 
    });

    Route::group(['middleware' => 'access:admin'], function(){
        Route::group(['prefix' => 'report'], function(){
            Route::get('finance', [ReportController::class, 'print_finance']);
            Route::get('appointment', [ReportController::class, 'print_appointment']);
            Route::get('doctor', [ReportController::class, 'print_doctor']);
            Route::get('patient', [ReportController::class, 'print_patient']);
            Route::get('branch', [ReportController::class, 'print_branch']);
            Route::get('department', [ReportController::class, 'print_department']);
            Route::get('specialist', [ReportController::class, 'print_specialist']);
        });
    });
});

Route::any('{any}', [Controller::class, 'index_mapping'])->where('any', '^(?!api).*$');
