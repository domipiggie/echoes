<?php
class MessageController
{
    private $db;
    private $message;

    public function __construct($dbConn)
    {
        $this->db = $dbConn;
        $this->message = new Message($dbConn);
    }

    public function handleSendMessage($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($data['channelID']) || !isset($data['content'])) {
                throw new ApiException('Missing required fields', 400);
            }

            if (empty(trim($data['content']))) {
                throw new ApiException('Message content cannot be empty', 400);
            }

            $user = AuthMiddleware::validateToken();

            if (!$this->message->hasChannelAccess($user->id, $data['channelID'])) {
                throw new ApiException('No access to this channel', 403);
            }

            $messageID = $this->message->createMessage(
                $data['channelID'],
                $user->id,
                $data['content']
            );

            if ($messageID) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'messageID' => $messageID,
                    'channelID' => $data['channelID'],
                    'userID' => $user->id,
                    'content' => $data['content']
                ]);
            } else {
                throw new ApiException('Failed to send message', 500);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to send message', 500);
        }
    }

    public function handleGetMessages($data)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            if (!isset($data['channelID'])) {
                throw new ApiException('Channel ID is required', 400);
            }

            $user = AuthMiddleware::validateToken();

            if (!$this->message->hasChannelAccess($user->id, $data['channelID'])) {
                throw new ApiException('No access to this channel', 403);
            }

            $offset = isset($data['offset']) ? intval($data['offset']) : 0;

            $messages = $this->message->getChannelMessages($data['channelID'], $offset);

            if ($messages !== false) {
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'messages' => $messages,
                    'offset' => $offset,
                    'limit' => 20
                ]);
            } else {
                throw new ApiException('Failed to fetch messages', 500);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to fetch messages', 500);
        }
    }
}
