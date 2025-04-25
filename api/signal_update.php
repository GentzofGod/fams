<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require '../vendor/autoload.php';

header('Content-Type: application/json');

// Safely decode the JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Debug to log what you're receiving (optional for development)
file_put_contents("debug.log", $input);

$course_code = $data['course_code'] ?? '';
$status = $data['status'] ?? '';

if (empty($status) || empty($course_code)) {
    echo json_encode(['signal_status' => 'error', 'message' => 'Missing course code']);
    exit;
}

// Mongo update
$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->attendance_system->esp_signals;

$collection->updateOne(
    ['course_code' => $course_code],
    ['$set' => ['status' => $status, 'timestamp' => date("Y-m-d H:i:s")]],
    ['upsert' => true]
);

echo json_encode(['signal_status' => 'success', 'message' => 'Signal updated']);
