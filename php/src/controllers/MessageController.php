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
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
            exit();
        }

        $user = AuthMiddleware::validateToken();

        if (!isset($data['channelID']) || !isset($data['content'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Missing required fields']);
            exit();
        }

        if (!$this->message->hasChannelAccess($user->id, $data['channelID'])) {
            http_response_code(403);
            echo json_encode(['message' => 'No access to this channel']);
            exit();
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
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to send message'
            ]);
        }
    }

    public function handleGetMessages($data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
            exit();
        }

        $user = AuthMiddleware::validateToken();

        if (!isset($data['channelID'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Channel ID is required']);
            exit();
        }

        if (!$this->message->hasChannelAccess($user->id, $data['channelID'])) {
            http_response_code(403);
            echo json_encode(['message' => 'No access to this channel']);
            exit();
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
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch messages'
            ]);
        }
    }
}