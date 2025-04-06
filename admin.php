<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$db = new Database();

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    if ($action === 'confirm') {
        $db->query("UPDATE bookings SET status = 'confirmed' WHERE id = ?", [$booking_id]);
    } elseif ($action === 'cancel') {
        $db->query("UPDATE bookings SET status = 'cancelled' WHERE id = ?", [$booking_id]);
    }
    
    header('Location: admin.php');
    exit();
}

// Get all bookings with user and room details
$bookings = $db->query("
    SELECT b.*, 
           u.name as guest_name, 
           u.email as guest_email, 
           u.phone as guest_phone, 
           rt.name as room_name
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    JOIN room_types rt ON b.room_type_id = rt.id
    ORDER BY b.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Clean up any empty email addresses
foreach ($bookings as &$booking) {
    if (empty($booking['guest_email'])) {
        $booking['guest_email'] = 'No email provided';
    }
}
unset($booking); // Break the reference with the last element

// Count pending bookings
$pending_count = 0;
foreach ($bookings as $booking) {
    if ($booking['status'] === 'pending') {
        $pending_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        .pending-row {
            background-color: rgba(251, 191, 36, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/nav.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 font-playfair">Admin Dashboard</h1>
            <?php if ($pending_count > 0): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 flex items-center rounded-md shadow-sm">
                <i class="fas fa-exclamation-circle text-yellow-500 mr-3 text-xl"></i>
                <span class="text-sm text-yellow-700">
                    <?php 
                    echo $pending_count . ' pending ' . ($pending_count === 1 ? 'booking' : 'bookings') . ' require' . ($pending_count === 1 ? 's' : '') . ' your attention';
                    ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Admin Actions Menu -->
        <div class="mb-8 flex flex-wrap gap-4">
            <a href="manage_rooms.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition duration-150">
                <i class="fas fa-bed mr-2"></i> Manage Rooms
            </a>
            <a href="#pending" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 shadow-sm transition duration-150">
                <i class="fas fa-clock mr-2"></i> View Pending Bookings
            </a>
            <a href="#all" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition duration-150">
                <i class="fas fa-list mr-2"></i> All Bookings
            </a>
        </div>
        
        <!-- Pending Bookings Section -->
        <div id="pending" class="mb-8">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-400 px-6 py-4 rounded-t-lg shadow-sm">
                <h2 class="text-xl text-white font-bold font-playfair">Pending Bookings</h2>
                <p class="text-yellow-100 text-sm">These bookings require confirmation</p>
            </div>
            
            <div class="bg-white shadow-lg rounded-b-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $has_pending = false;
                            foreach ($bookings as $booking):
                                if ($booking['status'] === 'pending'):
                                    $has_pending = true;
                            ?>
                            <tr class="pending-row">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $booking['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($booking['guest_name']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                        <?php echo htmlspecialchars($booking['guest_email']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-phone text-gray-400 mr-1"></i>
                                        <?php echo htmlspecialchars($booking['guest_phone']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                    <form method="POST">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 shadow-sm transition duration-150">
                                            <i class="fas fa-check mr-1.5"></i> Confirm
                                        </button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 shadow-sm transition duration-150">
                                            <i class="fas fa-times mr-1.5"></i> Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php 
                                endif;
                            endforeach; 
                            if (!$has_pending):
                            ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-check-circle text-3xl text-green-500 mb-3"></i>
                                        <p>No pending bookings to confirm.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- All Bookings Section -->
        <div id="all">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-400 px-6 py-4 rounded-t-lg shadow-sm">
                <h2 class="text-xl text-white font-bold font-playfair">All Bookings</h2>
                <p class="text-indigo-100 text-sm">Complete booking history</p>
            </div>
            
            <div class="bg-white shadow-lg rounded-b-lg overflow-hidden">
                <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($bookings as $booking): ?>
                            <tr class="<?php echo $booking['status'] === 'pending' ? 'pending-row' : ''; ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $booking['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($booking['guest_name']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                        <?php echo htmlspecialchars($booking['guest_email']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-phone text-gray-400 mr-1"></i>
                                        <?php echo htmlspecialchars($booking['guest_phone']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
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
                            </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-1">
                            <?php if ($booking['status'] === 'pending'): ?>
                                    <div class="flex space-x-2">
                                        <form method="POST">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <input type="hidden" name="action" value="confirm">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 shadow-sm transition duration-150">
                                                <i class="fas fa-check mr-1.5"></i> Confirm
                                            </button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 shadow-sm transition duration-150">
                                                <i class="fas fa-times mr-1.5"></i> Cancel
                                            </button>
                            </form>
                                    </div>
                                    <?php elseif ($booking['status'] !== 'cancelled'): ?>
                                    <form method="POST">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 shadow-sm transition duration-150">
                                            <i class="fas fa-times mr-1.5"></i> Cancel
                                        </button>
                            </form>
                                    <?php else: ?>
                                    <span class="text-gray-500 text-xs">No actions available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html> 