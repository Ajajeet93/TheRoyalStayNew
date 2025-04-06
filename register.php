<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        $db = new Database();
        
        // Check if email already exists
        $existing_user = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing_user) {
            $error = 'Email already registered';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $db->insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => $hashed_password,
                'phone' => $phone,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $success = 'Registration successful! Please login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .hotel-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/rooms/suite-1.jpg');
            background-size: cover;
            background-position: center;
        }
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/nav.php'; ?>

    <!-- Registration Form -->
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Left side - Registration form -->
        <div class="w-full md:w-1/2 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-md w-full space-y-8">
                <div>
                    <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 font-playfair">
                        Join Our Community
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Already have an account?
                        <a href="login.php" class="font-medium text-yellow-600 hover:text-yellow-500">
                            Sign in here
                        </a>
                    </p>
                </div>
                
                <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo htmlspecialchars($success); ?></p>
                            <p class="mt-2">
                                <a href="login.php" class="font-medium underline">
                                    Click here to login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <form class="mt-8 space-y-6" method="POST">
                    <div class="rounded-md shadow-sm space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="name" name="name" type="text" required 
                                    class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" 
                                    placeholder="Enter your full name">
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input id="email" name="email" type="email" required 
                                    class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" 
                                    placeholder="Enter your email address">
                            </div>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-gray-500">(optional)</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input id="phone" name="phone" type="tel" 
                                    class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" 
                                    placeholder="Enter your phone number">
                            </div>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password" name="password" type="password" required 
                                    class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" 
                                    placeholder="Create a password">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Password must be at least 6 characters long</p>
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="confirm_password" name="confirm_password" type="password" required 
                                    class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" 
                                    placeholder="Confirm your password">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required
                            class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            I agree to the <a href="#" class="text-yellow-600 hover:text-yellow-500">Terms of Service</a> and <a href="#" class="text-yellow-600 hover:text-yellow-500">Privacy Policy</a>
                        </label>
                    </div>

                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 shadow-lg transition-all duration-150">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-user-plus text-yellow-300 group-hover:text-yellow-200"></i>
                            </span>
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Right side - Hotel image and benefits -->
        <div class="hotel-bg hidden md:flex md:w-1/2 p-12 flex-col justify-center items-center text-white">
            <div class="text-center max-w-md mx-auto">
                <h1 class="text-5xl font-playfair font-bold mb-6">Become a Member</h1>
                <div class="w-24 h-1 bg-yellow-400 mx-auto mb-8"></div>
                
                <div class="space-y-6 text-left">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-check-circle text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xl font-medium">Exclusive Offers</h3>
                            <p class="mt-1">Access special rates and promotions</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-check-circle text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xl font-medium">Fast Booking</h3>
                            <p class="mt-1">Save your preferences for quicker reservations</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-check-circle text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xl font-medium">Manage Bookings</h3>
                            <p class="mt-1">View and modify your reservations easily</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html> 