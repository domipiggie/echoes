<?php
class MessageController
{
    private $dbConn;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function handleGetChannelMessages($channelId)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                throw new ApiException('Invalid method', 405);
            }

            $user = JWTTools::validateToken();

            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

            if ($limit > 100) {
                $limit = 100;
            }

            $response = MessageMiddleware::getChannelMessages($channelId, $user->id, $this->dbConn, $offset, $limit);

            echo $response;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getStatusCode());
        } catch (Exception $e) {
            throw new ApiException('Failed to get channel messages: ' . $e->getMessage(), 500);
        }
    }
}
