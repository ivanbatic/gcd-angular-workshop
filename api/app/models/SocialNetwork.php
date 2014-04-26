<?php

class SocialNetwork extends Eloquent
{
    public static $rules = array();
    protected $guarded = array();

    public static function getId($name)
    {
        switch (strtolower($name)) {
            case 'facebook':
                return 1;
                break;
        }
    }
}
