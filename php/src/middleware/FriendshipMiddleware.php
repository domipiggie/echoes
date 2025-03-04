<?php
class FriendshipMiddleware
{
    public static function validateUserIDs($friendship)
    {
        if ($friendship->getUser1ID() == $friendship->getUser2ID()){
            return false;
        }
        return true;
    }
}
?>