<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 12/21/13
 * Time: 5:20 AM
 */

class UserProfileController extends BaseApiController
{
    public function update()
    {
        $currentUser = \Auth::user();
        $updates     = \Input::get('updates');
        //        $updates['birthday'] = new \Carbon\Carbon($updates['birthdayDate']);

        $intersection = array_intersect_key($updates, array_flip(\User::$massFields));
        $currentUser->update($intersection);

        return $this->apiResponse->setField('user', $currentUser->toArray())->toJsonResponse();
    }
}