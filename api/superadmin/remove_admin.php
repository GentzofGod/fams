<?php
require '../config.php';
header("Content-Type: application/json");

use MongoDB\BSON\ObjectId;

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Admin ID is required"]);
    exit;
}

$idString = $_POST['id'];

try {
    $adminId = new ObjectId($idString);
    $result = $db->admins->deleteOne(['_id' => $adminId]);

    if ($result->getDeletedCount() > 0) {
        echo json_encode(["success" => true, "message" => "Admin deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Admin not found"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Invalid Admin ID: $idString"]);
}
