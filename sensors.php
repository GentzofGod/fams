<?php
require 'api/config.php'; // MongoDB connection

header('Content-Type: application/json');

if (!isset($_GET['sensor_id'])) {
    echo json_encode(['error' => 'Sensor ID is required']);
    exit;
}

$sensor_id = $_GET['sensor_id'];
$collection = $client->selectCollection('fams', 'sensors');

$sensor = $collection->findOne(['sensor_id' => $sensor_id]);

if (!$sensor) {
    echo json_encode(['error' => 'Sensor not found']);
    exit;
}

$response = [
    'sensor_id' => $sensor['sensor_id'],
    'description' => $sensor['description'],
    'location' => $sensor['location'],
    'enabled' => $sensor['enabled'],
    'type' => $sensor['type']
];

if ($sensor['type'] === 'toggle') {
    $response['status'] = $sensor['status'];
} else {
    $response['values'] = $sensor['values']; // in1, in2, in3, in4
}

echo json_encode($response);
