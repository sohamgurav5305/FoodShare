<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$project_root = dirname(__DIR__);
require_once $project_root . '/config/database.php';
require_once $project_root . '/classes/FoodSupportRequest.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$request = new FoodSupportRequest($db);
$request->ensureTable();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

if (
    empty($data['applicant_type']) ||
    empty($data['applicant_name']) ||
    empty($data['phone']) ||
    empty($data['address']) ||
    empty($data['people_count']) ||
    empty($data['food_needed'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if ($data['applicant_type'] === 'organization' && empty($data['organization_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Organization name is required']);
    exit;
}

$peopleCount = filter_var($data['people_count'], FILTER_VALIDATE_INT);
if ($peopleCount === false || $peopleCount < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'People count must be at least 1']);
    exit;
}

if (!empty($data['preferred_date'])) {
    $preferredDate = DateTime::createFromFormat('Y-m-d', $data['preferred_date']);
    $today = new DateTime('today');

    if (!$preferredDate || $preferredDate < $today) {
        http_response_code(400);
        echo json_encode(['error' => 'Preferred support date must be today or later']);
        exit;
    }
}

$request->applicant_type = $data['applicant_type'];
$request->applicant_name = $data['applicant_name'];
$request->organization_name = $data['organization_name'] ?? '';
$request->phone = $data['phone'];
$request->email = $data['email'] ?? '';
$request->address = $data['address'];
$request->city = $data['city'] ?? '';
$request->latitude = $data['latitude'] ?? null;
$request->longitude = $data['longitude'] ?? null;
$request->location_source = $data['location_source'] ?? null;
$request->people_count = $peopleCount;
$request->food_needed = $data['food_needed'];
$request->preferred_date = $data['preferred_date'] ?? null;
$request->additional_notes = $data['additional_notes'] ?? '';

$requestId = $request->create();

if ($requestId) {
    error_log("FOOD SUPPORT REQUEST: #{$requestId} {$request->applicant_name} ({$request->applicant_type}) - {$request->phone}");

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'request_id' => $requestId,
        'message' => 'Food support request submitted successfully. Our team will review it and contact you soon.'
    ]);
    exit;
}

http_response_code(500);
echo json_encode(['error' => 'Failed to submit food support request']);
?>
