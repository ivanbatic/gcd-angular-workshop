<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 12/19/13
 * Time: 10:32 PM
 */


namespace Auth\Social;

use Carbon\Carbon;

class FacebookUserProvider extends UserProvider
{
    /** @var \Facebook */
    protected $facebook;
    protected $me;

    public function __construct(\Facebook $facebook)
    {
        $this->facebook = $facebook;
    }

    public function setToken($token)
    {
        $this->facebook->setAccessToken($token);

        return parent::setToken($token);
    }

    public function fetchUser()
    {
        $me = $this->getMe();

        $this->setUserAttributes([
                'social_network_id' => \SocialNetwork::getId('facebook'),
                'social_id'         => $me['id'],
                'first_name'        => $me['first_name'],
                'last_name'         => $me['last_name'],
                'profile_url'       => $me['link'],
                'birthday'          => new Carbon($me['birthday']),
                'email'             => $me['email'],
                'gender'            => $me['gender']
            ]
        );

        return $this;
    }

    public function getMe()
    {
        if (!$this->me) {
            $this->me = $this->facebook->api('/me');
        }

        return $this->me;
    }

    public function pullProfilePhoto($realUserId)
    {
        $me           = $this->getMe();
        $picture      = file_get_contents("https://graph.facebook.com/{$me['id']}/picture?width=200&height=200");
        $relativePath = "/public/media/users/profile-photos/{$realUserId}.jpg";
        $absolutePath = public_path('..' . $relativePath);

        file_put_contents($absolutePath, $picture);

        return $relativePath;
    }
}