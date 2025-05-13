<?php
class UserinfoMiddleware
{
    public static function getUserInfo($userId)
    {
        $user = new User();
        $user->loadFromID($userId);

        $response = [
            "userID" => $user->getUserID(),
            "username" => $user->getUsername(),
            "displayName" => $user->getDisplayName(),
            "profilePicture" => $user->getProfilePicture()
        ];

        return $response;
    }

    public static function searchUser($name)
    {
        $user = new User();

        if (!$user->loadFromUsername($name)) {
            throw new ApiException("User not found", 404);
        }

        return [
            'userID' => $user->getUserID(),
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
            'profilePicture' => $user->getProfilePicture()
        ];
    }

    public static function getFriendList($userId)
    {
        $userInfo = new Userinfo();
        $friendList = $userInfo->getFriendList($userId);

        return $friendList;
    }

    public static function getFriendChannelList($userId)
    {
        $userInfo = new Userinfo();
        $friendChannelList = $userInfo->getFriendChannels($userId);

        return $friendChannelList;
    }

    public static function getGroupChannelList($userId)
    {
        $userInfo = new Userinfo();
        $groupChannelList = $userInfo->getGroupChannels($userId);

        return $groupChannelList;
    }
}
