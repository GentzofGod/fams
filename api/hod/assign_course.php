<?php
require '../config.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["success" => false, "message" => "Invalid request method"]);
  exit;
}

$courseId = $_POST['course_id'] ?? '';
$LecturerId = $_POST['Lecturer_id'] ?? '';

if (empty($courseId) || empty($LecturerId)) {
  echo json_encode(["success" => false, "message" => "Both course and Lecturer must be selected"]);
  exit;
}

try {
  $assignment = $db->assigned_courses->insertOne([
    'course_id' => new MongoDB\BSON\ObjectId($courseId),
    'Lecturer_id' => new MongoDB\BSON\ObjectId($LecturerId),
    'assigned_at' => new MongoDB\BSON\UTCDateTime()
  ]);

  echo json_encode(["success" => true, "message" => "Course assigned successfully"]);
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "Error assigning course"]);
}
