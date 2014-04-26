<?php

class Post extends Eloquent
{
    public static $rules = [];
    protected $guarded = [];

    public function user(){
        return $this->belongsTo('User');
    }
}
