<?php
require '../config.php';
header("Content-Type: application/json");

$data = json_decode(json_encode($_POST));

if (
    !isset(
        $data->name, $data->email, $data->password,
        $data->role, $data->department, $data->staff_number,
        $data->designation, $data->phone_number
    )
) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

if (!preg_match('/@gmail\.com$/', $data->email)) {
    echo json_encode(["success" => false, "message" => "Email must be a valid unilorin.edu.ng address"]);
    exit;
}

$collection = $db->admins;

// Check if email exists
$existingEmail = $collection->findOne(['email' => $data->email]);
if ($existingEmail) {
    echo json_encode(["success" => false, "message" => "Admin with this email already exists"]);
    exit;
}

// Check if staff number exists
$existingStaff = $collection->findOne(['staff_number' => $data->staff_number]);
if ($existingStaff) {
    echo json_encode(["success" => false, "message" => "Staff number already exists"]);
    exit;
}

// Ensure only one HOD per department
if ($data->role === 'HOD') {
    $existingHOD = $collection->findOne(['role' => 'HOD', 'department' => $data->department]);
    if ($existingHOD) {
        echo json_encode(["success" => false, "message" => "Only one HOD is allowed per department"]);
        exit;
    }
}

$firstLogin = ($data->role !== "superadmin");

$insert = $collection->insertOne([
    'name' => $data->name,
    'email' => $data->email,
    'staff_number' => $data->staff_number,
    'designation' => $data->designation,
    'phone_number' => $data->phone_number,
    'department' => $data->department,
    'role' => $data->role,
    'password' => password_hash($data->password, PASSWORD_BCRYPT),
    'first_login' => $firstLogin,
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(["success" => true, "message" => "Admin added successfully"]);
