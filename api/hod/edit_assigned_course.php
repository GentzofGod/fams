<?php
require '../config.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? '';
$newLecturerId = $data['Lecturer_id'] ?? '';

if (!$id || !$newLecturerId) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$collection = $db->assigned_courses;

$updateResult = $collection->updateOne(
    ['_id' => new MongoDB\BSON\ObjectId($id)],
    ['$set' => ['Lecturer_id' => new MongoDB\BSON\ObjectId($newLecturerId)]]
);

if ($updateResult->getMatchedCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}
?>
