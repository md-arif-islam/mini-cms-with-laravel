<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get( '/', function () {
    return view( 'welcome' );
} );

Auth::routes();

Route::get( '/home', [HomeController::class, 'index'] )->name( 'home' );
Route::get( '/shout', [HomeController::class, 'shoutHome'] )->name( 'shout' );
Route::get( '/shout/{nickname}', [HomeController::class, 'publicTimeline'] )->name( 'shout.public' );

Route::get( '/profile', [HomeController::class, 'profile'] )->name( 'shout.profile' );
Route::post( '/saveprofile', [HomeController::class, 'saveProfile'] )->name( 'shout.saveprofile' );
Route::post( '/savestatus', [HomeController::class, 'saveStatus'] )->name( 'shout.save' );