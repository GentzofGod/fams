<?php
require '../config.php';

$collection = $db->assigned_courses;

$cursor = $collection->find([]);

$result = [];

foreach ($cursor as $doc) {
    // Fetch course
    $course = $db->courses->findOne(['_id' => new MongoDB\BSON\ObjectId($doc['course_id'])]);
    $Lecturer = $db->admins->findOne(['_id' => new MongoDB\BSON\ObjectId($doc['Lecturer_id'])]);

    $result[] = [
        "id" => (string) $doc['_id'],
        "course_code" => $course['course_code'] ?? '',
        "course_title" => $course['course_title'] ?? '',
        "credit_load" =>$course['credit_load']??'',
        "Lecturer_name" => $Lecturer['name'] ?? '',
        "Lecturer_email" => $Lecturer['email'] ?? '',
        "phone_number" =>$Lecturer['phone_number'] ??''
    ];
}

echo json_encode(["assigned_courses" => $result]);
?>
