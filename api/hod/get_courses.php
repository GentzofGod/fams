<?php
require '../config.php';
header("Content-Type: application/json");

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 50;
$skip = ($page - 1) * $limit;

$collection = $db->courses;
$cursor = $collection->find([], ['skip' => $skip, 'limit' => $limit]);

$courses = [];
foreach ($cursor as $course) {
    $courses[] = [
        'id' => (string)($course->_id ?? ''),
        'course_code' => $course['course_code'] ?? '',
        'course_title' => $course['course_title'] ?? '',
        'credit_load'=> $course['credit_load']?? '',
        'created_at' => isset($course['created_at']) ? $course['created_at']->toDateTime()->format('Y-m-d H:i') : ''
    ];
}

echo json_encode([
    "success" => true,
    "courses" => $courses
]);
