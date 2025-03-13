<?php
header('Content-Type: application/json');

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data || !isset($data['messageData']) || !isset($data['channelID']) || !isset($data['accessibleUsers'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

error_log("Received notification request: " . json_encode($data));

$notificationDir = dirname(__DIR__) . '/src/WebSocket/notifications';
if (!is_dir($notificationDir)) {
    mkdir($notificationDir, 0755, true);
}

$notificationFile = $notificationDir . '/' . uniqid() . '.json';
file_put_contents($notificationFile, json_encode([
    'type' => 'admin_notification',
    'action' => 'new_message',
    'messageData' => $data['messageData'],
    'channelID' => $data['channelID'],
    'accessibleUsers' => $data['accessibleUsers'],
    'timestamp' => time()
]));

error_log("Saved notification to file: " . $notificationFile);

http_response_code(200);
echo json_encode(['status' => 'notification_queued']);
