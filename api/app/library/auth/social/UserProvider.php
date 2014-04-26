<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 12/19/13
 * Time: 9:59 PM
 */

namespace Auth\Social;


abstract class UserProvider
{
    protected $token;
    protected $userData = [
        'social_id'         => null,
        'social_network_id' => null,
        'profile_url'       => null,
        'photo_url'         => null,
        'first_name'        => null,
        'last_name'         => null,
        'gender'            => null,
        'birthday'          => null,
        'email'             => null,
        'country'           => null,
        'city'              => null,
        'user_id'           => null
    ];

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return UserProvider
     */
    public abstract function fetchUser();

    public function setUserAttribute($attribute, $value)
    {
        if (array_key_exists($attribute, $this->userData)) {
            $this->userData[$attribute] = $value;
        }

        return $this;
    }

    public function setUserAttributes(array $attributes)
    {
        $this->userData = array_merge($this->userData, array_intersect_key($attributes, $this->userData));

        return $this;
    }

    public function getUserAttributes()
    {
        return $this->userData;
    }

    public function getUserAttribute($attribute, $default = null)
    {
        return array_key_exists($attribute, $this->userData) ? $this->userData[$attribute] : $default;
    }

    public function mirrorToUser(\User $user, array $attributes = array())
    {
        $mirroring = is_empty($attributes) ? $this->userData : array_intersect_key($this->userData, $attributes);
    }

    public abstract function pullProfilePhoto($realUserId);

}