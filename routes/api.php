<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\SpecialistController;
use App\Http\Controllers\IdentityTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\DoctorController;

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
    Route::get('user', [AuthController::class, 'User'])->name('User')->middleware('jwt.verify');
});

Route::group(['prefix' => 'branch'], function(){
    Route::get('list', [BranchController::class, 'List'])->name('BranchList');
    Route::get('detail/{id}', [BranchController::class, 'Detail'])->name('BranchDetail');
    Route::post('create', [BranchController::class, 'Create'])->name('BranchCreate');
    Route::put('update/{id}', [BranchController::class, 'Update'])->name('BranchUpdate');
    Route::delete('delete/{id}', [BranchController::class, 'Delete'])->name('BranchDelete');
});

Route::group(['prefix' => 'departement'], function(){
    Route::get('list', [DepartementController::class, 'List'])->name('DepartementList');
    Route::get('detail/{id}', [DepartementController::class, 'Detail'])->name('DepartementDetail');
    Route::post('create', [DepartementController::class, 'Create'])->name('DepartementCreate');
    Route::put('update/{id}', [DepartementController::class, 'Update'])->name('DepartementUpdate');
    Route::delete('delete/{id}', [DepartementController::class, 'Delete'])->name('DepartementDelete');
});

Route::group(['prefix' => 'specialist'], function(){
    Route::get('list', [SpecialistController::class, 'List'])->name('SpecialistList');
    Route::get('detail/{id}', [SpecialistController::class, 'Detail'])->name('SpecialistDetail');
    Route::post('create', [SpecialistController::class, 'Create'])->name('SpecialistCreate');
    Route::put('update/{id}', [SpecialistController::class, 'Update'])->name('SpecialistUpdate');
    Route::delete('delete/{id}', [SpecialistController::class, 'Delete'])->name('SpecialistDelete');
});

Route::group(['prefix' => 'identitytype'], function(){
    Route::get('list', [IdentityTypeController::class, 'List'])->name('IdentityList');
    Route::get('detail/{id}', [IdentityTypeController::class, 'Detail'])->name('IdentityDetail');
    Route::post('create', [IdentityTypeController::class, 'Create'])->name('IdentityCreate');
    Route::put('update/{id}', [IdentityTypeController::class, 'Update'])->name('IdentityUpdate');
    Route::delete('delete/{id}', [IdentityTypeController::class, 'Delete'])->name('IdentityDelete');
});

Route::group(['prefix' => 'user'], function(){
    Route::get('list', [UserController::class, 'List'])->name('UserList');
    Route::get('detail/{id}', [UserController::class, 'Detail'])->name('UserDetail');
    Route::post('create', [UserController::class, 'Create'])->name('UserCreate');
    Route::put('update/{id}', [UserController::class, 'Update'])->name('UserUpdate');
    Route::delete('delete/{id}', [UserController::class, 'Delete'])->name('UserDelete');
});

Route::group(['prefix' => 'person'], function(){
    Route::get('list', [PersonController::class, 'List'])->name('PersonList');
    Route::get('detail/{id}', [PersonController::class, 'Detail'])->name('PersonDetail');
    Route::post('create', [PersonController::class, 'Create'])->name('PersonCreate');
    Route::put('update/{id}', [PersonController::class, 'Update'])->name('PersonUpdate');
    Route::delete('delete/{id}', [PersonController::class, 'Delete'])->name('PersonDelete');
});

Route::group(['prefix' => 'doctor'], function(){
    Route::get('list', [DoctorController::class, 'List'])->name('DoctorList');
    Route::get('detail/{id}', [DoctorController::class, 'Detail'])->name('DoctorDetail');
    Route::post('create', [DoctorController::class, 'Create'])->name('DoctorCreate');
    Route::put('update/{id}', [DoctorController::class, 'Update'])->name('DoctorUpdate');
    Route::delete('delete/{id}', [DoctorController::class, 'Delete'])->name('DoctorDelete');
});