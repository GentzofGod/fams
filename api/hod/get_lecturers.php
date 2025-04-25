<?php
require '../config.php';
header("Content-Type: application/json");

$Lecturers = [];
$cursor = $db->admins->find(["role" => "Lecturer"]);

foreach ($cursor as $Lecturer) {
  $Lecturers[] = [
    "id" => (string)$Lecturer->_id,
    "name" => $Lecturer->name,
    "email" => $Lecturer->email,
    "phone_number"=>$Lecturer->phone_number
  ];
}

echo json_encode(["success" => true, "Lecturers" => $Lecturers]);
