<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 12/19/13
 * Time: 9:02 PM
 */

namespace Auth;


use Auth\Social\FacebookUserProvider;

class UserGateway
{

    /**
     * @param $socialNetwork
     * @param $token
     *
     * @return \User
     */
    public function acceptSocialUserByToken($socialNetwork, $token)
    {
        /* @var $provider \Auth\Social\UserProvider */
        switch ($socialNetwork) {
            case 'Facebook':
                $provider = new FacebookUserProvider(\App::make('\Facebook'));
                break;
            default:
                throw new \InvalidArgumentException('Invalid Social Network Selected');
                break;
        }

        $userData   = $provider->setToken($token)->fetchUser();
        $socialUser = \SocialUser::
            where('social_network_id', $userData->getUserAttribute('social_network_id'))
            ->where('social_id', $userData->getUserAttribute('social_id'))
            ->first();

        // If he's never been here before with this account
        if (!($socialUser instanceof \SocialUser)) {
            // Check for a real user by email
            $realUser = \User::where('email', $userData->getUserAttribute('email'))
                ->first();

            if (!($realUser instanceof \User)) {


                // Create a real user
                $realUser = \User::create(
                    array_intersect_key(
                        $userData->getUserAttributes(),
                        array_flip(\User::$massFields)
                    )
                );

                $realUser->photo_url = $provider->pullProfilePhoto($realUser->id);
                $realUser->save();
            }
            $userData->setUserAttribute('user_id', $realUser->id);
            $socialUser = \SocialUser::create($userData->getUserAttributes());
        } else {
            $realUser = \User::find($socialUser->user_id);
            $socialUser->update(array_filter($userData->getUserAttributes()));
        }

        return $realUser;
    }

    public function facebookAccess(array $data)
    {
    }
} 