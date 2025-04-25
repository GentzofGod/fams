<?php
require '../config.php'; // adjust path as needed

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    echo json_encode(["success" => false, "message" => "Missing ID"]);
    exit;
}

$id = $data->id;

try {
    $collection = $db->assigned_courses;
    $deleteResult = $collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

    if ($deleteResult->getDeletedCount() > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "No course found"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
