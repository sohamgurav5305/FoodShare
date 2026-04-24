<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$project_root = dirname(__DIR__);
require_once $project_root. '/config/database.php';
require_once $project_root. '/classes/Statistics.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$stats = new Statistics($db);

try {
    $dashboard_stats = $stats->getDashboardStats();
    
    $response = [
        'success' => true,
        'data' => [
            'people_fed' => (int)$dashboard_stats['people_served'],
            'meals_served' => (int)$dashboard_stats['meals_distributed'],
            'volunteers' => (int)$dashboard_stats['active_volunteers'],
            'locations' => 10, // Static for now
            'total_donations' => (int)$dashboard_stats['total_donations'],
            'total_amount' => (float)$dashboard_stats['total_amount'],
            'food_donations' => (int)$dashboard_stats['food_donations']
        ],
        'last_updated' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch statistics']);
}
?>