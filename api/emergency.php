<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$project_root = dirname(__DIR__);
require_once $project_root. '/config/database.php';
require_once $project_root. '/classes/EmergencyRequest.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$emergency = new EmergencyRequest($db);
$emergency->ensureLocationColumns();

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (empty($data['requester_name']) || empty($data['phone']) || 
    empty($data['address']) || empty($data['family_size']) || 
    empty($data['urgent_reason'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Set emergency request properties
$emergency->requester_name = $data['requester_name'];
$emergency->phone = $data['phone'];
$emergency->email = $data['email'] ?? '';
$emergency->address = $data['address'];
$emergency->latitude = $data['latitude'] ?? null;
$emergency->longitude = $data['longitude'] ?? null;
$emergency->location_source = $data['location_source'] ?? null;
$emergency->family_size = (int)$data['family_size'];
$emergency->urgent_reason = $data['urgent_reason'];
$emergency->dietary_restrictions = $data['dietary_restrictions'] ?? '';

// Create emergency request
$request_id = $emergency->create();

if ($request_id) {
    // Send immediate alerts to emergency response team
    sendEmergencyAlerts($emergency, $request_id);
    
    $response = [
        'success' => true,
        'request_id' => $request_id,
        'message' => 'Emergency request submitted. Our team will contact you within 30 minutes.',
        'hotline' => '+91 9356822437'
    ];
    
    http_response_code(201);
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit emergency request']);
}

function sendEmergencyAlerts($emergency, $request_id) {
    $alert = "🚨 EMERGENCY FOOD REQUEST #{$request_id}\n";
    $alert .= "Name: {$emergency->requester_name}\n";
    $alert .= "Phone: {$emergency->phone}\n";
    $alert .= "Address: {$emergency->address}\n";
    $alert .= "Family Size: {$emergency->family_size}\n";
    $alert .= "Reason: {$emergency->urgent_reason}";
    
    // Log for immediate attention
    error_log("EMERGENCY ALERT: " . $alert);
    
    // Here you would integrate with SMS service for immediate alerts
    // Example: Twilio, AWS SNS, etc.
    
    return true;
}
?>
