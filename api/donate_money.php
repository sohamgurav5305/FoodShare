<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$project_root = dirname(__DIR__);
require_once $project_root. '/config/database.php';
require_once $project_root. '/classes/MonetaryDonation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
if (!isset($data['name'], $data['email'], $data['phone'], $data['amount'], $data['payment_method'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
if (!$db) {
    die(json_encode(['error' => 'Database connection failed']));
}

$donation = new MonetaryDonation($db);

// Set donation properties
$donation->donor_name     = $data['name'];
$donation->email          = $data['email'];
$donation->phone          = $data['phone'];
$donation->amount         = $data['amount'];
$donation->payment_method = $data['payment_method'];
$donation->payment_status = 'pending';
$donation->transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
$donation->notes          = $data['notes'] ?? '';

// Create donation record
$donation_id = $donation->create();

if ($donation_id) {
    echo json_encode([
        'success' => true,
        'donation_id' => $donation_id,
        'transaction_id' => $donation->transaction_id,
        'message' => 'Thank you for your generous donation!'
    ]);
    http_response_code(201);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to process donation']);
}
?>
