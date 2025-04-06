<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();

// Get all room types from database
$all_room_types = $db->query("SELECT * FROM room_types ORDER BY price_per_night ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get room type if specified
$room_type_id = isset($_GET['room_type']) ? (int)$_GET['room_type'] : null;

// Get user_id if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Debug information
echo "<!-- Debug: User ID in booking.php = " . $user_id . " -->";

// Fetch room type details if specified
$room_type = null;
if ($room_type_id) {
    $room_type = $db->query("SELECT * FROM room_types WHERE id = ?", [$room_type_id])->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id']; // Ensure we have the user_id
    $room_type_id = $_POST['room_type'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];
    $special_requests = $_POST['special_requests'];
    $guest_name = $_POST['guest_name'];
    $guest_email = $_POST['guest_email'];
    $guest_phone = $_POST['guest_phone'];

    // Debug information
    echo "<!-- Debug: Creating booking with user_id = " . $user_id . " -->";

    // Validate dates
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    
    if ($check_in_date >= $check_out_date) {
        $error = "Check-out date must be after check-in date";
    } else {
        // Calculate total amount
        $nights = $check_in_date->diff($check_out_date)->days;
        
        // Get room price based on room type from database
        $room_price_query = $db->query("SELECT price_per_night FROM room_types WHERE id = ?", [$room_type_id]);
        $room_price_data = $room_price_query->fetch(PDO::FETCH_ASSOC);
        
        if (!$room_price_data) {
            $error = "Invalid room type selected";
        } else {
            $room_price = $room_price_data['price_per_night'];
            $total_amount = $nights * $room_price;

            // Create booking
            try {
                // Debug information
                echo "<!-- Debug: Creating booking with user_id = " . $user_id . " -->";
                echo "<!-- Debug: Guest email = " . $guest_email . " -->";
                echo "<!-- Debug: Room type ID = " . $room_type_id . " -->";
                echo "<!-- Debug: Check-in date = " . $check_in . " -->";
                echo "<!-- Debug: Check-out date = " . $check_out . " -->";
                echo "<!-- Debug: Total amount = " . $total_amount . " -->";

                $db->query(
                    "INSERT INTO bookings (user_id, room_type_id, check_in, check_out, guests, special_requests, 
                    guest_name, guest_email, guest_phone, total_amount, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
                    [$user_id, $room_type_id, $check_in, $check_out, $guests, $special_requests, 
                    $guest_name, $guest_email, $guest_phone, $total_amount]
                );
                
                // Debug information
                echo "<!-- Debug: Booking created successfully -->";
                
                // Check the most recent booking
                $latest_booking = $db->query("
                    SELECT * FROM bookings 
                    ORDER BY id DESC 
                    LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);
                echo "<!-- Debug: Latest booking: " . print_r($latest_booking, true) . " -->";
                
                // Set success message instead of redirecting
                $success = "Booking request submitted successfully! Your booking is pending confirmation by our staff. You can view your booking status in My Bookings.";
            } catch (Exception $e) {
                // Debug information
                echo "<!-- Debug: Error creating booking: " . $e->getMessage() . " -->";
                $error = "An error occurred while creating your booking. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        .booking-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/rooms/deluxe-room-2.jpg');
            background-size: cover;
            background-position: center;
        }
        input:focus, select:focus, textarea:focus {
            --tw-ring-color: #FBBF24;
            border-color: #FBBF24;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/nav.php'; ?>

    <!-- Hero Banner -->
    <div class="booking-bg py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold text-white font-playfair mb-4">Book Your Stay</h1>
            <div class="w-24 h-1 bg-yellow-400 mx-auto mb-6"></div>
            <p class="text-xl text-gray-200 max-w-3xl mx-auto">Experience luxury and comfort at <?php echo SITE_NAME; ?>. Reserve your perfect getaway today.</p>
        </div>
    </div>

    <!-- Booking Form Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 -mt-12">
        <div class="max-w-4xl mx-auto">
            <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md mb-6" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="ml-3">
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-md mb-6" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="ml-3">
                        <p><?php echo $success; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="bg-white shadow-xl rounded-lg overflow-hidden">
                <!-- Form Steps Indicators -->
                <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 p-4 text-white">
                    <div class="flex justify-between">
                        <div class="flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center bg-white text-yellow-600 rounded-full mr-2 font-semibold">1</span>
                            <span>Room Selection</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center bg-white/30 text-white rounded-full mr-2 font-semibold">2</span>
                            <span>Guest Details</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center bg-white/30 text-white rounded-full mr-2 font-semibold">3</span>
                            <span>Confirmation</span>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <!-- Room Selection -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-playfair font-bold text-gray-800 mb-6">Select Your Room</h2>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="room_type">
                                Room Type <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="room_type" id="room_type" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 pl-10" required>
                                    <option value="">Select a room type</option>
                                    <?php foreach ($all_room_types as $rt): ?>
                                    <option value="<?php echo $rt['id']; ?>" <?php echo $room_type_id == $rt['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($rt['name']); ?> - $<?php echo number_format($rt['price_per_night'], 2); ?>/night
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-bed text-gray-400"></i>
                                </div>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="check_in">
                                    Check-in Date <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="check_in" id="check_in" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 pl-10" placeholder="Select date" required>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="check_out">
                                    Check-out Date <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="check_out" id="check_out" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 pl-10" placeholder="Select date" required>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="guests">
                                Number of Guests <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="guests" id="guests" min="1" max="4" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 pl-10" required>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-user-friends text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guest Information -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-playfair font-bold text-gray-800 mb-6 flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center bg-yellow-500 text-white rounded-full mr-2 text-sm">2</span>
                            Guest Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="guest_name">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="guest_name" id="guest_name" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 pl-10" required>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="guest_phone">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="tel" name="guest_phone" id="guest_phone" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 pl-10" required>
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="guest_email">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="email" name="guest_email" id="guest_email" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 pl-10" required>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Requests -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-playfair font-bold text-gray-800 mb-6 flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center bg-yellow-500 text-white rounded-full mr-2 text-sm">3</span>
                            Additional Requests
                        </h2>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="special_requests">
                                Special Requests
                            </label>
                            <div class="relative">
                                <textarea name="special_requests" id="special_requests" rows="4" class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 pl-10" placeholder="Let us know if you have any special requirements or preferences..."></textarea>
                                <div class="absolute top-3 left-0 flex items-center pl-3">
                                    <i class="fas fa-concierge-bell text-gray-400"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">We'll do our best to accommodate your requests, subject to availability.</p>
                        </div>
                        
                        <div class="flex items-center mb-6">
                            <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                            <label for="terms" class="ml-2 block text-sm text-gray-900">
                                I agree to the <a href="#" class="text-yellow-600 hover:text-yellow-700 underline">Terms and Conditions</a>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-between border-t border-gray-200 pt-6">
                        <a href="rooms.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i> View Rooms
                        </a>
                        <button type="submit" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Complete Booking <i class="fas fa-check ml-2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Why Book With Us -->
    <div class="bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center font-playfair text-gray-900 mb-12">Why Book With Us</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 text-yellow-600 mb-4">
                        <i class="fas fa-percent text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Best Rate Guarantee</h3>
                    <p class="text-gray-600">Find a lower rate elsewhere? We'll match it and give you an additional 10% off.</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 text-yellow-600 mb-4">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Flexible Cancellation</h3>
                    <p class="text-gray-600">Plans changed? No problem. We offer free cancellation up to 24 hours before check-in.</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 text-yellow-600 mb-4">
                        <i class="fas fa-gift text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Exclusive Benefits</h3>
                    <p class="text-gray-600">Book directly with us and enjoy complimentary breakfast and room upgrades when available.</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Initialize date pickers
        flatpickr("#check_in", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                // Set checkout minimum date to the day after checkin
                const nextDay = new Date(selectedDates[0]);
                nextDay.setDate(nextDay.getDate() + 1);
                check_out_picker.set('minDate', nextDay);
                
                // If checkout date is before new checkin date, update it
                const currentCheckout = new Date(check_out_picker.selectedDates[0]);
                if (currentCheckout && currentCheckout <= selectedDates[0]) {
                    check_out_picker.setDate(nextDay);
                }
            }
        });

        const check_out_picker = flatpickr("#check_out", {
            minDate: "today",
            dateFormat: "Y-m-d"
        });
        
        // Pre-fill guest information if user is logged in
        <?php if (isset($_SESSION['user_id'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // You can add code here to pre-fill user information from session if needed
        });
        <?php endif; ?>
    </script>
</body>
</html> 