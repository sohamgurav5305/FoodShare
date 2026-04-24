<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$project_root = dirname(__DIR__);
require_once $project_root. '/config/database.php';
require_once $project_root. '/classes/FoodDonation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$foodDonation = new FoodDonation($db);
$foodDonation->ensureLocationColumns();

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (empty($data['contact_name']) || empty($data['contact_phone']) || 
    empty($data['food_type']) || empty($data['pickup_date']) || 
    empty($data['pickup_address'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Validate pickup date
$pickup_date = DateTime::createFromFormat('Y-m-d', $data['pickup_date']);
if (!$pickup_date || $pickup_date < new DateTime()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid pickup date']);
    exit;
}

// Set food donation properties
$foodDonation->contact_name = $data['contact_name'];
$foodDonation->contact_phone = $data['contact_phone'];
$foodDonation->contact_email = $data['contact_email'] ?? '';
$foodDonation->food_type = $data['food_type'];
$foodDonation->description = $data['description'];
$foodDonation->estimated_quantity = $data['estimated_quantity'] ?? '';
$foodDonation->pickup_date = $data['pickup_date'];
$foodDonation->pickup_time = $data['pickup_time'] ?? '10:00';
$foodDonation->pickup_address = $data['pickup_address'];
$foodDonation->pickup_latitude = $data['pickup_latitude'] ?? null;
$foodDonation->pickup_longitude = $data['pickup_longitude'] ?? null;
$foodDonation->location_source = $data['location_source'] ?? null;
$foodDonation->special_instructions = $data['special_instructions'] ?? '';

// Create food donation record
$donation_id = $foodDonation->create();

if ($donation_id) {
    // Send notification to pickup team
    $notification_sent = sendPickupNotification($foodDonation);
    
    $response = [
        'success' => true,
        'donation_id' => $donation_id,
        'message' => 'Food donation scheduled successfully! We\'ll contact you soon.',
        'pickup_date' => $data['pickup_date'],
        'notification_sent' => $notification_sent
    ];
    
    http_response_code(201);
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to schedule food donation']);
}

function sendPickupNotification($donation) {
    // Send SMS or email to pickup team
    $message = "New food donation scheduled:\n";
    $message .= "Contact: {$donation->contact_name}\n";
    $message .= "Phone: {$donation->contact_phone}\n";
    $message .= "Food Type: {$donation->food_type}\n";
    $message .= "Pickup Date: {$donation->pickup_date}\n";
    $message .= "Address: {$donation->pickup_address}";
    
    error_log("FOOD PICKUP NOTIFICATION: " . $message);
    return true;
}
?>
