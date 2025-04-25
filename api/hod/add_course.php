<?php
require '../config.php';
header("Content-Type: application/json");

if (!isset($_POST['course_code']) || !isset($_POST['course_title'])) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

$course_code = trim($_POST['course_code']);
$course_title = trim($_POST['course_title']);
$credit_load = trim($_POST['credit_load']);

if ($course_code === '' || $course_title === ''|| $credit_load === '') {
    echo json_encode(["success" => false, "message" => "Empty values are not allowed"]);
    exit;
}

$collection = $db->courses;
$existing = $collection->findOne(["course_code" => $course_code]);

if ($existing) {
    echo json_encode(["success" => false, "message" => "Course already exists"]);
    exit;
}

$collection->insertOne([
    "course_code" => $course_code,
    "course_title" => $course_title,
    "credit_load" =>$credit_load,
    "created_at" => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(["success" => true, "message" => "Course added successfully"]);
