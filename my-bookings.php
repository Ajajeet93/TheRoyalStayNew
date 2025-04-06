<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = $_SESSION['user_id'];

// Debug information
echo "<!-- Debug: User ID = " . $user_id . " -->";

// Get user's bookings with room type details
echo "<!-- Debug: Starting to fetch bookings for user_id = " . $user_id . " -->";

$bookings = $db->query("
    SELECT b.*, rt.name as room_name, rt.image_url, rt.name as room_type
    FROM bookings b
    JOIN room_types rt ON b.room_type_id = rt.id
    WHERE b.user_id = ?
    ORDER BY b.check_in DESC
", [$user_id])->fetchAll(PDO::FETCH_ASSOC);

// Debug information
echo "<!-- Debug: Number of bookings = " . count($bookings) . " -->";
echo "<!-- Debug: Bookings data: " . print_r($bookings, true) . " -->";

// Let's also check if there are any bookings at all in the database
$all_bookings = $db->query("SELECT * FROM bookings")->fetchAll(PDO::FETCH_ASSOC);
echo "<!-- Debug: All bookings in database: " . print_r($all_bookings, true) . " -->";

// Let's check the user's information
$user = $db->query("SELECT * FROM users WHERE id = ?", [$user_id])->fetch(PDO::FETCH_ASSOC);
echo "<!-- Debug: User information: " . print_r($user, true) . " -->";

// Function to get room image URL with fallback
function getRoomImageUrl($imageUrl, $roomType) {
    // If image URL is empty or invalid, use a default image based on room type
    if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        // Convert room type to lowercase for case-insensitive comparison
        $roomTypeLower = strtolower($roomType);
        
        // Default images for known room types
        if (strpos($roomTypeLower, 'standard') !== false) {
            return 'images/rooms/standard1.jpg';
        } elseif (strpos($roomTypeLower, 'deluxe') !== false) {
            return 'images/rooms/deluxe-room-1.jpg';
        } elseif (strpos($roomTypeLower, 'suite') !== false) {
            return 'images/rooms/suite-1.jpg';
        } elseif (strpos($roomTypeLower, 'basic') !== false) {
            return 'images/rooms/standard-room-2.jpg';
        } else {
            // Default fallback for any other room types
            return 'images/rooms/default-room.jpg';
        }
    }
    return $imageUrl;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        .booking-list-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/rooms/suite-2.jpeg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/nav.php'; ?>

    <!-- Hero Banner -->
    <div class="booking-list-bg py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold text-white font-playfair mb-4">My Bookings</h1>
            <div class="w-24 h-1 bg-yellow-400 mx-auto mb-6"></div>
            <p class="text-xl text-gray-200 max-w-3xl mx-auto">View and manage your stay reservations at <?php echo SITE_NAME; ?>.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 -mt-8">
        <?php if (empty($bookings)): ?>
            <div class="bg-white shadow-lg rounded-lg p-8 text-center">
                <i class="fas fa-calendar-times text-5xl text-yellow-500 mb-4"></i>
                <h2 class="text-xl font-medium text-gray-900 font-playfair">No Bookings Found</h2>
                <p class="mt-2 text-gray-600 max-w-md mx-auto">
                    You haven't made any bookings yet. Explore our luxurious rooms and book your perfect stay.
                </p>
                <div class="mt-6">
                    <a href="rooms.php" class="inline-flex items-center px-5 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 transition duration-150">
                        Browse Rooms
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($bookings as $booking): ?>
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        <div class="md:flex">
                            <div class="md:flex-shrink-0">
                                <?php 
                                $imageUrl = getRoomImageUrl($booking['image_url'], $booking['room_name']);
                                ?>
                                <img class="h-56 w-full object-cover md:w-56" 
                                     src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                     alt="<?php echo htmlspecialchars($booking['room_name']); ?>"
                                     onerror="this.onerror=null; this.src='images/rooms/default-room.jpg';">
                            </div>
                            <div class="p-8 flex-1">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-2xl font-semibold text-gray-900 font-playfair"><?php echo htmlspecialchars($booking['room_name']); ?></h2>
                                    <span class="px-3 py-1 text-sm font-medium rounded-full
                                        <?php
                                        switch ($booking['status']) {
                                            case 'confirmed':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'cancelled':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                <p class="mt-1 text-gray-500"><?php echo htmlspecialchars($booking['room_type']); ?></p>
                                
                                <div class="mt-6 grid grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Check-in</p>
                                        <p class="mt-1 text-base text-gray-900"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Check-out</p>
                                        <p class="mt-1 text-base text-gray-900"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Guests</p>
                                        <p class="mt-1 text-base text-gray-900"><?php echo $booking['guests']; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Total Amount</p>
                                        <p class="mt-1 text-base text-gray-900 font-medium">$<?php echo number_format($booking['total_amount'], 2); ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($booking['status'] === 'confirmed'): ?>
                                    <div class="mt-6">
                                        <button type="button" onclick="cancelBooking(<?php echo $booking['id']; ?>)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150">
                                            <i class="fas fa-times mr-2"></i>
                                            Cancel Booking
                                        </button>
                                    </div>
                                <?php elseif ($booking['status'] === 'pending'): ?>
                                    <div class="mt-6 flex items-center p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                        <i class="fas fa-clock text-yellow-500 mr-3 text-lg"></i>
                                        <div>
                                            <p class="text-sm text-yellow-700">Your booking is awaiting confirmation by our staff.</p>
                                            <p class="text-xs text-yellow-600 mt-1">You will receive notification once it's confirmed.</p>
                                        </div>
                                    </div>
                                <?php elseif ($booking['status'] === 'cancelled'): ?>
                                    <div class="mt-6 flex items-center p-4 bg-red-50 border border-red-200 rounded-md">
                                        <i class="fas fa-ban text-red-500 mr-3 text-lg"></i>
                                        <p class="text-sm text-red-700">This booking has been cancelled.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    function cancelBooking(bookingId) {
        if (confirm('Are you sure you want to cancel this booking?')) {
            // Send AJAX request to cancel booking
            fetch('cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ booking_id: bookingId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to cancel booking');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the booking');
            });
        }
    }
    </script>
</body>
</html> 