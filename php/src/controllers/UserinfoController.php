<?php
class UserinfoController{
    public static function handleGetUserInfo($id, $dbConn)
    {
        $user = new User($dbConn);
        $user->loadFromID($id);

        $response = array("userID"=>$user->getUserID(), "username"=>$user->getUsername(), "displayName"=>$user->getDisplayName(), "profilePicture"=>$user->getProfilePicture());
        echo json_encode($response);
    }
}
?>