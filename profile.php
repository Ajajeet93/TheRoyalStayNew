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

$user_id = $_SESSION['user_id'];

// Get user data using direct database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=3307",
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
    // Use a direct query to fetch user data including email and admin status
    $stmt = $pdo->prepare("SELECT id, name, email, phone, created_at, is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Ensure all necessary fields exist and set default values if needed
    $user['name'] = $user['name'] ?? '';
    $user['email'] = $user['email'] ?? '';
    $user['phone'] = $user['phone'] ?? '';
    $user['created_at'] = $user['created_at'] ?? date('Y-m-d H:i:s');
    $user['is_admin'] = $user['is_admin'] ?? 0;
    
    // If user ID is 2, use a hardcoded email (for testing)
    if ($user_id == 2 && (empty($user['email']) || $user['email'] == '')) {
        $user['email'] = 'ajeetyadav16022004@gmail.com';
    }
    
    // If user is admin, always use admin@hotel.com email
    if ($user['is_admin'] == 1) {
        $user['email'] = 'admin@hotel.com';
    }
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $user = [
        'name' => 'Error loading profile',
        'email' => '',
        'phone' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'is_admin' => 0
    ];
}

$db = new Database(); // Create database instance for handling form submission

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Use the existing email since we've removed it from the form
    $email = $user['email'];
    
    $errors = [];
    
    // Validate required fields
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($phone)) {
        $errors[] = "Phone is required";
    }
    
    // Handle password change if requested
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } else {
            // Verify current password
            $user_data = $db->query("SELECT password FROM users WHERE id = ?", [$user_id])->fetch(PDO::FETCH_ASSOC);
            if (!$user_data || !password_verify($current_password, $user_data['password'])) {
                $errors[] = "Current password is incorrect";
            }
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
        
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        }
    }
    
    // If no errors, update user information
    if (empty($errors)) {
        $update_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Add password to update if it was changed
        if (!empty($new_password)) {
            $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $db->update('users', $update_data, ['id' => $user_id]);
        
        // Update session data
        $_SESSION['user_name'] = $name;
        
        $success_message = "Profile updated successfully";
        
        // Reload updated user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Ensure user data has phone
if (empty($user['phone']) || $user['phone'] === '') {
    $user['phone'] = '1234567890';  // Default phone number
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        .profile-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/rooms/presidential-suite-1.jpg');
            background-size: cover;
            background-position: center;
        }
        input:focus, select:focus, textarea:focus {
            --tw-ring-color: #F59E0B; /* yellow-500 */
            border-color: #F59E0B; /* yellow-500 */
        }
        .btn-yellow {
            background-color: #F59E0B; /* yellow-500 */
            color: white;
        }
        .btn-yellow:hover {
            background-color: #D97706; /* yellow-600 */
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/nav.php'; ?>

    <!-- Hero Banner -->
    <div class="profile-bg py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-block rounded-full bg-white/10 p-2 mb-4">
                <i class="fas fa-user-circle text-yellow-400 text-5xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white font-playfair mb-2">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></h1>
            <div class="w-24 h-1 bg-yellow-400 mx-auto mb-4"></div>
            <p class="text-gray-200">Manage your profile and preferences</p>
            
            <!-- Contact Info -->
            <div class="flex flex-col items-center mt-5 space-y-3" style="margin-top: 20px;">
                <?php if (!empty($user['phone'])): ?>
                <p class="bg-black bg-opacity-30 text-yellow-300 text-2xl px-6 py-3 rounded-full shadow-md">
                    <i class="fas fa-phone text-yellow-400 mr-3"></i><?php echo htmlspecialchars($user['phone']); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 -mt-8">
        <div class="max-w-4xl mx-auto">
            <!-- Profile Navigation Tabs -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
                <div class="flex border-b border-gray-200">
                    <button class="px-6 py-4 border-b-2 border-yellow-500 text-yellow-600 font-medium flex items-center">
                        <i class="fas fa-user-cog mr-2"></i> Profile Settings
                    </button>
                    <a href="my-bookings.php" class="px-6 py-4 text-gray-600 hover:text-yellow-600 font-medium flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i> My Bookings
                    </a>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800">Please fix the following errors:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-md mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-lg font-medium"><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Profile Settings Form -->
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 px-6 py-4">
                    <h2 class="text-xl text-white font-semibold">Personal Information</h2>
                </div>
                
                <form method="POST" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required
                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required
                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Display Section -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope text-yellow-500 text-xl mr-3"></i>
                            </div>
                            <div>
                                <p class="text-lg font-medium text-gray-800">
                                <?php
                                // Special handling for user ID 2 (Ajeet)
                                if ($user_id == 2) {
                                    echo 'ajeetyadav16022004@gmail.com';
                                }
                                // Always show admin@hotel.com for admin users
                                else if (isset($user['is_admin']) && $user['is_admin'] == 1) {
                                    echo 'admin@hotel.com';
                                }
                                // For other users
                                else if (!empty($user['email'])) {
                                    echo htmlspecialchars($user['email']);
                                } else {
                                    echo '<span class="text-gray-500">No email available</span>';
                                }
                                ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-playfair font-bold text-gray-800">Change Password</h2>
                            <span class="text-sm text-gray-500">Optional</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input type="password" name="current_password" id="current_password" value=""
                                           class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                </div>
                            </div>
                            
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-key text-gray-400"></i>
                                    </div>
                                    <input type="password" name="new_password" id="new_password" value=""
                                           class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long</p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-check-circle text-gray-400"></i>
                                    </div>
                                    <input type="password" name="confirm_password" id="confirm_password" value=""
                                           class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-200 flex justify-between items-center">
                        <a href="index.php" class="inline-flex items-center px-4 py-2 border border-yellow-500 shadow-sm text-sm font-medium rounded-md text-yellow-600 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Homepage
                        </a>
                        <div class="text-sm text-gray-500">
                            Account created on: <?php echo date('F j, Y', strtotime($user['created_at'] ?? 'now')); ?>
                        </div>
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Account Settings -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-8">
                <div class="bg-gradient-to-r from-red-600 to-red-400 px-6 py-4">
                    <h2 class="text-xl text-white font-semibold">Account Settings</h2>
                </div>
                
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Delete Account</h3>
                            <p class="text-sm text-gray-500">Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-red-600 shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-trash-alt mr-2"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            const passwordFields = document.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => {
                // Add functionality if needed
            });
        });
    </script>
</body>
</html> 