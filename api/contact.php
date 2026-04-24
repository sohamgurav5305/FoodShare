<?php
// api/contact.php

header("Content-Type: application/json; charset=UTF-8");



header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
       http_response_code(200);
       exit();
   }

// Go up one level from the current 'api' directory to find the project root
$project_root = dirname(__DIR__);

// Include DB and class files using the correct path
include_once $project_root . '/config/database.php';
include_once $project_root . '/classes/ContactMessage.php';

 

// Create DB connection

$database = new Database();
   $db = $database->getConnection();

   if (!$db) {
       echo json_encode([
           "success" => false,
           "error" => "Database connection failed"
       ]);
       exit();
   }
// Create ContactMessage instance
$contact = new ContactMessage($db);

// Get posted JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid input"
    ]);
    exit();
}

// Assign values
$contact->name    = $input['name'] ?? '';
$contact->email   = $input['email'] ?? '';
$contact->phone   = $input['phone'] ?? '';
$contact->subject = $input['subject'] ?? '';
$contact->message = $input['message'] ?? '';

// Validate required fields
if (empty($contact->name) || empty($contact->email) || empty($contact->subject) || empty($contact->message)) {
    echo json_encode([
        "success" => false,
        "error" => "Missing required fields"
    ]);
    exit();
}

// Create message
$messageId = $contact->create();

if ($messageId) {
    echo json_encode([
        "success" => true,
        "message_id" => $messageId,
        "message" => "Message stored successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Unable to save message"
    ]);
}
