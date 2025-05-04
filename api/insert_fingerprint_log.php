<?php
// insert_fingerprint_log.php
require 'config.php'; // include your MongoDB setup

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if (!isset($_POST['student_id']) || !isset($_POST['course_code'])) {
    http_response_code(400);
    echo "Missing parameters";
    exit;
}

$studentId = (int)$_POST['student_id'];
$courseCode = $_POST['course_code'];
$timestamp = new MongoDB\BSON\UTCDateTime();

$log = [
    'student_id' => $studentId,
    'course_code' => $courseCode,
    'timestamp' => $timestamp
];

$collection = $db->fingerprint_logs;
$result = $collection->insertOne($log);

if ($result->getInsertedCount() > 0) {
    echo "Log inserted for student_id: $studentId";
} else {
    echo "Failed to insert log.";
}
