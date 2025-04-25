<?php
require_once '../config.php';
header('Content-Type: application/json');
session_start();

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents("php://input"), true);
$courseId = new MongoDB\BSON\ObjectId($data['course_id'] ?? null); // ensure course_id is an ObjectId
$matricNo = strtoupper(trim($data['matric_no'] ?? ''));

if (!$courseId || !$matricNo) {
    echo json_encode(['success' => false, 'message' => 'Missing course or matric number']);
    exit;
}

$lecturerId = $_SESSION['admin_id'] ?? null;
if (!$lecturerId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $mongo = (new MongoDB\Client)->fams;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$student = $mongo->students->findOne(['matric_no' => $matricNo]);

if (!$student) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

// Check if already enrolled
$already = $mongo->enrolled_students->findOne([
    'course_id' => $courseId, // course_id is ObjectId
    'matric_no' => $matricNo
]);

if ($already) {
    echo json_encode(['success' => false, 'message' => 'Student already enrolled']);
    exit;
}

// Insert into enrolled_students
$mongo->enrolled_students->insertOne([
    'course_id' => $courseId,
    'matric_no' => $matricNo,
    'student_id' => $student['student_id'],
    'added_by' => new MongoDB\BSON\ObjectId($lecturerId),
    'added_at' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(['success' => true, 'message' => 'Student added successfully']);
