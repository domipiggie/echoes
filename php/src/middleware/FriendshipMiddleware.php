<?php
class FriendshipMiddleware
{
    public static function validateUserIDs($friendship)
    {
        if ($friendship->user1ID == $friendship->user2ID){
            return false;
        }
        return true;
    }
}
?>