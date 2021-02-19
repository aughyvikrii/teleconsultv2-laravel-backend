<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SpecialistController;
use App\Http\Controllers\IdentityTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\MarriedStatusController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\ScheduleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::any('/{path?}', function(){
//     return response()->json([
//         'data' => '200'
//     ], 400);
// })
// ->where('path', '.*')
// ->name('react');

Route::group(['prefix' => 'auth'], function(){
    Route::post('register', [AuthController::class, 'Register'])->name('Register');
    Route::post('login', [AuthController::class, 'Login'])->name('Login');
    Route::post('resend_link_verif', [AuthController::class, 'ResendLinkVerification'])->name('ResendLinkVerification');
    Route::get('user', [AuthController::class, 'User'])->name('User')->middleware('jwt.verify');
});

Route::match(['POST', 'GET'], '/living_area', [PersonController::class, 'living_area'])->name('LivingArea');

Route::group(['middleware' => 'access'],  function(){

    Route::group(['prefix' => 'branch'], function(){
        Route::match(['GET', 'POST'], 'list', [BranchController::class, 'List'])->name('BranchList');
        Route::get('detail/{id}', [BranchController::class, 'Detail'])->name('BranchDetail');

        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [BranchController::class, 'Create'])->name('BranchCreate');
            Route::put('update/{id}', [BranchController::class, 'Update'])->name('BranchUpdate');
            Route::delete('delete/{id}', [BranchController::class, 'Delete'])->name('BranchDelete');
        });
    });
    
    Route::group(['prefix' => 'department'], function(){
        Route::match(['GET', 'POST'], 'list', [DepartmentController::class, 'List'])->name('DepartmentList');
        Route::get('detail/{id}', [DepartmentController::class, 'Detail'])->name('DepartmentDetail');

        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [DepartmentController::class, 'Create'])->name('DepartmentCreate');
            Route::put('update/{id}', [DepartmentController::class, 'Update'])->name('DepartmentUpdate');
            Route::delete('delete/{id}', [DepartmentController::class, 'Delete'])->name('DepartmentDelete');
        });
    });
    
    Route::group(['prefix' => 'specialist'], function(){
        Route::match(['GET', 'POST'], 'list', [SpecialistController::class, 'List'])->name('SpecialistList');
        Route::get('detail/{id}', [SpecialistController::class, 'Detail'])->name('SpecialistDetail');

        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [SpecialistController::class, 'Create'])->name('SpecialistCreate');
            Route::put('update/{id}', [SpecialistController::class, 'Update'])->name('SpecialistUpdate');
            Route::delete('delete/{id}', [SpecialistController::class, 'Delete'])->name('SpecialistDelete');
        });
    });
    
    Route::group(['prefix' => 'identitytype'], function(){
        Route::match(['GET', 'POST'], 'list', [IdentityTypeController::class, 'List'])->name('IdentityList');
        Route::get('detail/{id}', [IdentityTypeController::class, 'Detail'])->name('IdentityDetail');

        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [IdentityTypeController::class, 'Create'])->name('IdentityCreate');
            Route::put('update/{id}', [IdentityTypeController::class, 'Update'])->name('IdentityUpdate');
            Route::delete('delete/{id}', [IdentityTypeController::class, 'Delete'])->name('IdentityDelete');
        });
    });
    
    Route::group(['prefix' => 'user'], function(){
        Route::match(['GET', 'POST'], 'list', [UserController::class, 'List'])->name('UserList');
        Route::get('detail/{id}', [UserController::class, 'Detail'])->name('UserDetail');

        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [UserController::class, 'Create'])->name('UserCreate');
            Route::put('update/{id}', [UserController::class, 'Update'])->name('UserUpdate');
            Route::delete('delete/{id}', [UserController::class, 'Delete'])->name('UserDelete');
        });
    });

    Route::group(['prefix' => 'family'], function(){
        Route::match(['GET', 'POST'], 'list', [PersonController::class, 'FamilyList'])->name('FamilyList');
        Route::post('add', [PersonController::class, 'FamilyAdd'])->name('FamilyAdd');
        Route::get('detail/{id}', [PersonController::class, 'FamilyDetail'])->name('FamilyDetail');
        Route::put('update/{code?}', [PersonController::class, 'FamilyUpdate']);
        Route::delete('delete/{id}', [PersonController::class, 'FamilyMemberDelete'])->name('FamilyMemberDelete');
    });
    
    Route::group(['prefix' => 'person'], function(){
        Route::match(['GET', 'POST'], 'list', [PersonController::class, 'List'])->name('PersonList');
        Route::get('detail/{id}', [PersonController::class, 'Detail'])->name('PersonDetail');

        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [PersonController::class, 'Create'])->name('PersonCreate');
            Route::put('update/{id}', [PersonController::class, 'Update'])->name('PersonUpdate');
            Route::delete('delete/{id}', [PersonController::class, 'Delete'])->name('PersonDelete');
        });
    });
    
    Route::group(['prefix' => 'doctor'], function(){
        Route::match(['GET', 'POST'], 'list', [DoctorController::class, 'List'])->name('DoctorList');
        Route::get('detail/{id}', [DoctorController::class, 'Detail'])->name('DoctorDetail');

        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [DoctorController::class, 'Create'])->name('DoctorCreate');
            Route::put('update/{id}', [DoctorController::class, 'Update'])->name('DoctorUpdate');
            Route::delete('delete/{id}', [DoctorController::class, 'Delete'])->name('DoctorDelete');
            Route::match(['GET', 'POST'], '{id}/schedules', [ScheduleController::class, 'DoctorSchedule'])->name('DoctorSchedules');
            Route::post('{id}/schedule/add', [ScheduleController::class, 'DoctorScheduleAdd'])->name('DoctorScheduleAdd');
        });
    });

    Route::group(['prefix' => 'schedule'], function(){
        Route::match(['GET', 'POST'], 'list', [ScheduleController::class, 'List'])->name('ScheduleList');
        Route::put('{id}', [ScheduleController::class, 'Update'])->name('ScheduleUpdate');
    });
    
    Route::group(['prefix' => 'religion'], function(){
        Route::match(['GET', 'POST'], 'list', [ReligionController::class, 'List'])->name('ReligionList');
        Route::get('detail/{id}', [ReligionController::class, 'Detail'])->name('ReligionDetail');

        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [ReligionController::class, 'Create'])->name('ReligionCreate');
            Route::put('update/{id}', [ReligionController::class, 'Update'])->name('ReligionUpdate');
            Route::delete('delete/{id}', [ReligionController::class, 'Delete'])->name('ReligionDelete');
        });
    });
    
    Route::group(['prefix' => 'married_status'], function(){
        Route::match(['GET', 'POST'], 'list', [MarriedStatusController::class, 'List'])->name('MarriedStatusList');
        Route::get('detail/{id}', [MarriedStatusController::class, 'Detail'])->name('MarriedStatusDetail');
        
        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [MarriedStatusController::class, 'Create'])->name('MarriedStatusCreate');
            Route::put('update/{id}', [MarriedStatusController::class, 'Update'])->name('MarriedStatusUpdate');
            Route::delete('delete/{id}', [MarriedStatusController::class, 'Delete'])->name('MarriedStatusDelete');
        });
    });
    
    Route::group(['prefix' => 'title'], function(){
        Route::match(['GET', 'POST'], 'list', [TitleController::class, 'List'])->name('TitleList');
        Route::get('detail/{id}', [TitleController::class, 'Detail'])->name('TitleDetail');
        Route::group(['middleware' => 'access:admin'], function(){
            Route::post('create', [TitleController::class, 'Create'])->name('TitleCreate');
            Route::put('update/{id}', [TitleController::class, 'Update'])->name('TitleUpdate');
            Route::delete('delete/{id}', [TitleController::class, 'Delete'])->name('TitleDelete');
        });
    });
});