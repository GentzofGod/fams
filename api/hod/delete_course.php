<?php
require '../config.php';
header("Content-Type: application/json");

if (!isset($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Course ID is required"]);
    exit;
}

try {
    $id = new MongoDB\BSON\ObjectId($_POST['id']);
    $collection = $db->courses;

    $delete = $collection->deleteOne(['_id' => $id]);

    if ($delete->getDeletedCount() > 0) {
        echo json_encode(["success" => true, "message" => "Course deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Course not found"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Invalid Course ID"]);
}
