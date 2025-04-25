<?php
require '../vendor/autoload.php'; // if using Composer MongoDB driver

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$fingerprint_id = $data['fingerprint_id'] ?? '';
$course_code = $data['course_code'] ?? '';

if (empty($fingerprint_id) || empty($course_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->attendance_system->attendance;

$timestamp = date("Y-m-d H:i:s");

$record = [
    'fingerprint_id' => $fingerprint_id,
    'course_code' => $course_code,
    'timestamp' => $timestamp
];

$result = $collection->insertOne($record);

echo json_encode([
    'status' => 'success',
    'inserted_id' => (string)$result->getInsertedId(),
    'message' => 'Attendance recorded'
]);
