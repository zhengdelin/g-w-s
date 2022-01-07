<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenshinController;
use App\Http\Controllers\DataController;
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

Route::get('/', function () {
    return view('welcome');
});

// Route::get('index', [Controller::class, 'index']);

Route::get('genshin', [GenshinController::class, 'index']);
Route::post('genshin/wish', [GenshinController::class, 'wish']);
Route::get('genshin/wish_test', [GenshinController::class, 'wish_test']);
Route::post('genshin/set_focus',[GenshinController::class,'set_focus']);
Route::post('genshin/set_cur_pool',[GenshinController::class,'set_cur_pool']);
Route::post('genshin/reset',[GenshinController::class,'reset']);
Route::post('genshin/set_optional_pool',[GenshinController::class,'set_optional_pool']);

Route::get('genshin/data/index',[DataController::class,'index']);
Route::post('genshin/data/set_cur_pool',[DataController::class,'set_cur_pool']);
Route::get('genshin/data/search',[DataController::class,'search']);
Route::post('genshin/data/add_pool',[DataController::class,'add_pool']);
Route::get('genshin/data/add_crwpimg',[DataController::class,'add_crwpimg']);
Route::get('genshin/data/add_std',[DataController::class,'add_std']);
Route::get('genshin/data/video', [DataController::class,'video']);
Route::get('genshin/data/search',[DataController::class,'search']);
Route::post('genshin/data/search_crwp',[DataController::class,'search_crwp']);
// Route::get('data/add_poolimg',[DataController::class,'add_crwpimg']);
