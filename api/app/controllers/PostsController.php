<?php

class PostsController extends BaseApiController {


	public function getAll()
	{
		$posts = Post::get();
        $this->apiResponse->setField('posts', $posts->toArray());
        return $this->apiResponse->toJsonResponse();
	}

}