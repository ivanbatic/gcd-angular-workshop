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

Route::filter('authenticated', function () {
        if (!\Auth::check()) {
            $response = new ApiResponse(new \Illuminate\Support\MessageBag([]));

            return $response->setStatusCode(403)->setField('error','You are not logged in')->toJsonResponse();
        }
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

Route::group(array('before' => 'authenticated'), function()
{
    Route::post('posts', 'PostsController@createPost');
    Route::get('posts', 'PostsController@getPosts');
});



Route::any('{all}', function () {
        $response = Response::make(['error' => 'Invalid request'], 404);

        return $response;
    }
)->where('all', '.*');