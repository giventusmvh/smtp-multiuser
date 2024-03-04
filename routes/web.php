<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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


Route::middleware(['guest'])->group(function(){
    Route::view('/','halaman_depan/index');
    Route::get("/sesi",[AuthController::class,'index'])->name('auth');
    Route::post("/sesi",[AuthController::class,'login']);
    Route::get("/reg",[AuthController::class,'create'])->name('registrasi');
    Route::post("/reg",[AuthController::class,'register']);
    Route::get('/verify/{verify_key}',[AuthController::class,'verify']);
});

Route::middleware(['auth'])->group(function(){
    Route::redirect('/home','/user');
    Route::get('/admin',[AdminController::class,'index'])->name('admin')->middleware('UserAkses:admin');
    Route::get('/user',[UserController::class,'index'])->name('user')->middleware('UserAkses:user');
});



