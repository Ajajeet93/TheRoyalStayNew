<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to cancel bookings']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$booking_id = $input['booking_id'] ?? null;

if (!$booking_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

$db = new Database();
$user_id = $_SESSION['user_id'];

// Verify booking belongs to user
$booking = $db->query("SELECT * FROM bookings WHERE id = ? AND user_id = ?", [$booking_id, $user_id])->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit();
}

// Check if booking can be cancelled
if ($booking['status'] !== 'confirmed') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'This booking cannot be cancelled']);
    exit();
}

if (strtotime($booking['check_in']) <= time()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot cancel a booking that has already started']);
    exit();
}

// Update booking status
try {
    $db->query(
        "UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
        [$booking_id]
    );
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to cancel booking']);
} 