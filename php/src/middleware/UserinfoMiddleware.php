<?php
class UserinfoMiddleware
{
    public static function getUserInfo($userId, $dbConn)
    {
        $user = new User($dbConn);
        $user->loadFromID($userId);

        $response = [
            "userID" => $user->getUserID(),
            "username" => $user->getUsername(),
            "displayName" => $user->getDisplayName(),
            "profilePicture" => $user->getProfilePicture()
        ];

        return $response;
    }

    public static function searchUser($name, $dbConn)
    {
        $user = new User($dbConn);
        
        if (!$user->loadFromUsername($name)) {
            throw new ApiException("User not found", 404);
        }

        return json_encode([
            'userID' => $user->getUserID(),
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
            'profilePicture' => $user->getProfilePicture()
        ]);
    }

    public static function getFriendList($userId, $dbConn)
    {
       $userInfo = new Userinfo($dbConn);
       $friendList = $userInfo->getFriendList($userId);

       return $friendList;
    }

    public static function getFriendChannelList($userId, $dbConn)
    {
        $userInfo = new Userinfo($dbConn);
        $friendChannelList = $userInfo->getFriendChannels($userId);

        return $friendChannelList;
    }

    public static function getGroupChannelList($userId, $dbConn)
    {
        $userInfo = new Userinfo($dbConn);
        $groupChannelList = $userInfo->getGroupChannels($userId);

        return $groupChannelList;
    }
}
