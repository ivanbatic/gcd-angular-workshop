<?php

class SocialUser extends Eloquent
{
    public static $rules = array();
    protected $guarded = array();

    public function socialNetwork(){
        return $this->belongsTo('SocialNetwork');
    }
}
