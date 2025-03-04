<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['json.response']], function () {
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
    // Public routes

    Route::post('/register', 'Api\Auth\AuthController@register');
    Route::post('/login', 'Api\Auth\AuthController@login');
    Route::post('/send-otp', 'Api\Auth\ForgetPasswordController@sendOtp');
    Route::post('/reset-password', 'Api\Auth\ForgetPasswordController@resetPassword');
    Route::post('/verify-otp', 'Api\Auth\VerificationController@verifyOtp');
    Route::post('/resend-otp', 'Api\Auth\VerificationController@resendOtp');
    Route::get('/act_mod', 'Api\UserController@act_mod');

    // Blog Routes
    Route::get('/blogs', 'Api\ArticlesController@index');
    Route::get('/blogs/{id}', 'Api\ArticlesController@single');

    Route::get('/courses', 'Api\CourseController@index');
    Route::get('/courses/{id}', 'Api\CourseController@single');


    Route::post('/filter_consultation', 'Api\ConsultationController@getTime');
    Route::post('/filter_consultation_page', 'Api\ConsultationController@consultation_page');
    Route::get('/consultationtype', 'Api\ConsultationController@getType');


    Route::get('/books', 'Api\BooksController@index');
    Route::get('/books/{id}', 'Api\BooksController@single');

     // Authenticated vendor routes
     Route::group(['middleware' => 'auth:vendor'], function () {
        Route::post('/blogs/createComment', 'Api\ArticlesController@createCommentes');
        Route::post('/books/createComment', 'Api\BooksController@createCommentes');
        Route::get('/course/lectures/{id}', 'Api\CourseController@lectures');
        Route::get('/course/lecture/{video_id}/navigation', 'Api\CourseController@getNextAndPreviousVideo');
        Route::post('/join_to_group/{id}', 'Api\ChatController@joinToGroup');
        Route::middleware(['auth:sanctum', 'update.last_seen'])->get('/group_details/{id}', 'Api\ChatController@group');
        Route::post('/logout', 'Api\Auth\AuthController@logout');

        //----
        Route::post('/group/{group_id}/send-message', 'Api\ChatController@sendMessage');
        Route::get('/group/{group_id}/messages', 'Api\ChatController@getMessages');
        Route::post('/message/{message_id}/mark-read', 'Api\ChatController@markAsRead');



    });

    Route::get('/available_groups', 'Api\ChatController@groups');

});



