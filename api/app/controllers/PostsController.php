<?php

class PostsController extends BaseApiController
{


    public function getPosts()
    {
        $posts = \Auth::user()->posts;
        return $this->apiResponse->setField('posts', $posts->toArray())->toJsonResponse();
    }

    public function createPost()
    {
        $user = \Auth::user();

        $validator = \Validator::make(\Input::all(), [
                'content' => 'required|min:5'
            ]
        );
        if($validator->passes()){
            $post          = new Post();
            $post->user_id = $user->id;
            $post->content = \Input::get('content', '');
            $post->save();
            return $this->apiResponse->setField('post', $post->toArray())->toJsonResponse();
        } else {
            return $this->apiResponse->setMessageBag($validator->messages())->toJsonResponse();
        }

    }

}