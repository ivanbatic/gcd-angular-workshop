<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

App::bind('\Facebook', function ($app) {
        return new Facebook(\Config::get('app.social.facebook'));
    }
);

Route::post('register', 'LoginController@register');
Route::post('login', 'LoginController@login');
Route::post('login/facebook', 'LoginController@facebook');
Route::get('current-user', 'LoginController@getCurrentUser');
Route::any('logout', 'LoginController@logout');

Route::post('profile/update', 'UserProfileController@update');
Route::get('user/{id}/videos', 'UserProfileController@getVideos');
Route::post('user/{id}/videos/add', 'UserProfileController@addVideo');

Route::get('posts', 'PostsController@getAll');

Route::any('{all}', function () {
        $response = Response::make(['error'=> 'Invalid request'], 404);

        return $response;
    }
)->where('all', '.*');