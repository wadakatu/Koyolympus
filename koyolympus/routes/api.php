<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Admin専用
Route::group(['middleware' => ['auth']], function () {
    //写真アップロードメソッド
    Route::post('/photo/upload', 'v1\ImageController@uploadPhoto')->name('upload.photo');
    //写真削除メソッド
    Route::post('/photo/remove', 'v1\ImageController@removePhoto')->name('remove.photo');
});

//ログインメソッド
Route::post('/login', 'Auth\LoginController@login')->name('login');
//ログアウトメソッド
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
//ログインユーザー返却メソッド
Route::get('/user', function () {
    return Auth::user();
})->name('user');
//Biz Inquiries送信処理
Route::post('/bizinq/send', 'v1\BizInquiriesController@sendBizInquiries')->name('bizInq.send');
//写真取得メソッド
Route::get('/photos', 'v1\ImageController@getPhoto')->name('get.photo');
//ランダム写真取得メソッド
Route::get('/randomPhotos', 'v1\ImageController@getRandomPhoto')->name('get.randomPhoto');

Route::post('/like', 'v1\LikeController@likePhoto')->name('like.photo');

Route::post('/unlike', 'v1\LikeController@unlikePhoto')->name('unlike.photo');
