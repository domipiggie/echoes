<?php
class MessageMiddleware
{
    public static function getChannelMessages($channelId, $userId, $offset = 0, $limit = 20)
    {
        try {
            $message = new Message();

            if (!$message->hasChannelAccess($userId, $channelId)) {
                throw new ApiException('You do not have access to this channel', 403);
            }

            $messages = $message->getChannelMessages($channelId, $offset, $limit);

            $messagesWithUserInfo = [];

            foreach ($messages as $msg) {
                $userDetails = UserinfoMiddleware::getUserInfo($msg['userID']);
                $msg['user'] = $userDetails;
                $messagesWithUserInfo[] = $msg;
            }

            return [
                'messages' => $messagesWithUserInfo,
                'pagination' => [
                    'offset' => (int)$offset,
                    'limit' => (int)$limit,
                    'total' => count($messagesWithUserInfo)
                ]
            ];
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get channel messages: ' . $e->getMessage(), 500);
        }
    }
}
