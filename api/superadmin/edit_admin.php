<?php
require '../config.php';
header("Content-Type: application/json");

$id = $_POST['admin_id'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$role = $_POST['role'] ?? '';
$department = $_POST['department'] ?? '';
$staff_number = $_POST['staff_number'] ?? '';
$designation = $_POST['designation'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';

if (!$id || !$name || !$email || !$role || !$department || !$staff_number || !$designation || !$phone_number) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

try {
    $adminId = new MongoDB\BSON\ObjectId($id);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Invalid admin ID"]);
    exit;
}

$collection = $db->admins;

// 🚨 Check for existing HOD
if ($role === "HOD") {
    $existingHOD = $collection->findOne([
        "role" => "HOD",
        "department" => $department,
        "_id" => ['$ne' => $adminId]
    ]);
    if ($existingHOD) {
        echo json_encode(["success" => false, "message" => "An HOD already exists in this department"]);
        exit;
    }
}

// 🚨 Optional: Ensure staff number is unique across other admins
$existingStaff = $collection->findOne([
    "staff_number" => $staff_number,
    "_id" => ['$ne' => $adminId]
]);
if ($existingStaff) {
    echo json_encode(["success" => false, "message" => "Staff number already in use"]);
    exit;
}

// ✅ Perform update
$update = $collection->updateOne(
    ["_id" => $adminId],
    ['$set' => [
        "name" => $name,
        "email" => $email,
        "role" => $role,
        "department" => $department,
        "staff_number" => $staff_number,
        "designation" => $designation,
        "phone_number" => $phone_number
    ]]
);

if ($update->getModifiedCount() > 0) {
    echo json_encode(["success" => true, "message" => "Admin updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "No changes made"]);
}
