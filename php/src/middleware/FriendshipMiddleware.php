<?php
class FriendshipMiddleware
{
    public static function validateUserIDs($friendship)
    {
        try {
            if ($friendship->getUser1ID() == $friendship->getUser2ID()) {
                return false;
            }
            return true;
        } catch (Exception $e) {
            throw new ApiException('Failed to validate user IDs', 500);
        }
    }
}
