<?php
require 'api/config.php'; // MongoDB connection
header('Content-Type: application/json');

// Decode incoming JSON from ESP32
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['course_code']) || !isset($data['student_id'])) {
    echo json_encode(['error' => 'course_code and student_id are required']);
    exit;
}

$courseCode = $data['course_code'];
$studentId = $data['student_id'];
$timestamp = date('Y-m-d H:i:s');

// Select the collection
$collection = $client->selectCollection('fams', 'fingerprint_logs');

// Insert attendance log
$insertResult = $collection->insertOne([
    'course_code' => $courseCode,
    'student_id' => $studentId,
    'timestamp' => $timestamp
]);

// Respond with success
echo json_encode([
    'message' => 'Attendance log recorded successfully',
    'inserted_id' => (string) $insertResult->getInsertedId()
]);
