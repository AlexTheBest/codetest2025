<?php
// Backend entry point
require_once 'config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    'success' => true,
    'message' => 'Welcome to the API'
];

echo json_encode($response);
?>
