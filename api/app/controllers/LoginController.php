<?php

class LoginController extends BaseApiController
{

    public function facebook()
    {
        $token   = \Input::get('access_token');
        $gateway = new \Auth\UserGateway();
        $user    = $gateway->acceptSocialUserByToken('Facebook', $token);
        \Auth::login($user);
        return $this->apiResponse
            ->setField('user', $user->toArray())
            ->toJsonResponse();
    }

    public function login()
    {
        \Auth::attempt([
                'email'    => \Input::get('email'),
                'password' => \Input::get('password')
            ], true
        );

        if (\Auth::check()) {
            return $this->apiResponse
                ->setField('user', \Auth::user()->toArray())
                ->toJsonResponse();
        } else {
            return $this->apiResponse
                ->setStatusCode(403)
                ->addMessage('auth_error', 'Invalid Credentials')
                ->toJsonResponse();
        }
    }

    public function logout()
    {
        \Auth::logout();

        return $this->apiResponse->toJsonResponse();
    }

    public function register()
    {
        $email          = \Input::get('email');
        $password       = \Input::get('password');
        $repeatPassword = \Input::get('repeat_password');

        $validator = \Validator::make([
                'email'           => $email,
                'password'        => $password,
                'repeat_password' => $repeatPassword
            ], [
                'email'           => ['required', 'email', 'unique:users'],
                'password'        => ['required', 'min:6'],
                'repeat_password' => ['required', 'same:password']
            ]
        );
        if ($validator->passes()) {
            $user = \User::create([
                    'email'    => $email,
                    'password' => \Hash::make($password)
                ]
            );
            $this->apiResponse->setField('user', $user->toArray());
        } else {
            $this->apiResponse->setMessageBag($validator->messages())->setStatusCode(403);
        }

        return $this->apiResponse->toJsonResponse();
    }

    public function getCurrentUser()
    {
        if (\Auth::check()) {
            $user = \Auth::user();
            $user->load('socialUsers', 'socialUsers.socialNetwork');
            return $this->apiResponse->setField('user', $user->toArray())->toJsonResponse();
        } else {
            return $this->apiResponse->setStatusCode(401)->toJsonResponse();
        }

    }
}
