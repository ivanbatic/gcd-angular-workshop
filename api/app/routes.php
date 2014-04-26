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
//\Auth::loginUsingId(1);
App::bind('\Facebook', function ($app) {
        return new Facebook(\Config::get('app.social.facebook'));
    }
);

Route::filter('authenticated', function () {
        if (!\Auth::check()) {
            $response = new ApiResponse(new \Illuminate\Support\MessageBag(array()));

            return $response->setStatusCode(403)->addMessage('error', 'You are not logged in')->toJsonResponse();
        }
    }
);

Route::any('register', 'LoginController@register');
Route::any('login', 'LoginController@login');
Route::any('login/facebook', 'LoginController@facebook');
Route::any('current-user', 'LoginController@getCurrentUser');
Route::any('logout', 'LoginController@logout');

Route::group(array('before' => 'authenticated'), function()
{
    Route::post('profile/update', 'UserProfileController@update');
    Route::post('posts', 'PostsController@createPost');
    Route::get('posts', 'PostsController@getPosts');
});



Route::any('{all}', function () {
        $response = Response::make(array('error' => 'Invalid request'), 404);

        return $response;
    }
)->where('all', '.*');