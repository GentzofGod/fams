<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require '../vendor/autoload.php';

header('Content-Type: application/json');

$course_code = $_POST['course_code'] ?? '';

if (empty($course_code)) {
    echo json_encode(['signal_status' => 'error', 'message' => 'Missing course code']);
    exit;
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->attendance_system->esp_signals;

$signal = $collection->findOne(['course_code' => $course_code]);

if ($signal) {
    echo json_encode([
        'signal_status' => 'success',
        'course_code' => $signal['course_code'],
        'signal_status' => $signal['signal_status'],
        'timestamp' => $signal['timestamp']
    ]);
} else {
    echo json_encode(['signal_status' => 'none']);
}
