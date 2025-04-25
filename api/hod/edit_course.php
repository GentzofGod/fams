<?php
require '../config.php';
header("Content-Type: application/json");

if (!isset($_POST['id'], $_POST['course_code'], $_POST['course_title'])) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

try {
    $id = new MongoDB\BSON\ObjectId($_POST['id']);
    $collection = $db->courses;

    $update = $collection->updateOne(
        ['_id' => $id],
        ['$set' => [
            'course_code' => $_POST['course_code'],
            'course_title' => $_POST['course_title']
        ]]
    );

    echo json_encode(["success" => true, "message" => "Course updated successfully"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Failed to update course"]);
}
