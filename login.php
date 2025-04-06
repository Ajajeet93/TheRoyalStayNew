<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $db = new Database();
            $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
            
            // Debug information
            error_log("Login attempt for email: " . $email);
            
            if ($user) {
                error_log("User found in database");
                if (password_verify($password, $user['password'])) {
                    error_log("Password verified successfully");
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    
                    header('Location: index.php');
                    exit();
                } else {
                    error_log("Password verification failed");
                    $error = 'Invalid email or password';
                }
            } else {
                error_log("User not found in database");
                $error = 'Invalid email or password';
            }
        } catch (Exception $e) {
            error_log("Database error during login: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .hotel-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/rooms/deluxe-room-1.jpg');
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

    <?php if (!isset($_SESSION['user_id'])): ?>
    <!-- Login Form -->
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Left side - Hotel image and branding -->
        <div class="hotel-bg hidden md:flex md:w-1/2 p-12 flex-col justify-center items-center text-white">
            <div class="text-center max-w-md mx-auto">
                <h1 class="text-5xl font-playfair font-bold mb-6">Welcome Back</h1>
                <p class="text-xl mb-8">Experience luxury and comfort at <?php echo SITE_NAME; ?></p>
                <div class="w-24 h-1 bg-yellow-400 mx-auto mb-8"></div>
                <p class="italic">"The perfect gateway for your perfect getaway"</p>
            </div>
        </div>
        
        <!-- Right side - Login form -->
        <div class="w-full md:w-1/2 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-md w-full space-y-8">
                <div>
                    <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 font-playfair">
                        Sign in to your account
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Or
                        <a href="register.php" class="font-medium text-yellow-600 hover:text-yellow-500">
                            create a new account
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

                <form class="mt-8 space-y-6" method="POST">
                    <div class="rounded-md shadow-sm space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input id="email" name="email" type="email" required 
                                    class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" 
                                    placeholder="Enter your email">
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
                                    placeholder="Enter your password">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" 
                                class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Remember me
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-yellow-600 hover:text-yellow-500">
                                Forgot your password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 shadow-lg transition-all duration-150">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-sign-in-alt text-yellow-300 group-hover:text-yellow-200"></i>
                            </span>
                            Sign in
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Already Logged In Message -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 hotel-bg">
        <div class="max-w-md w-full space-y-8 text-center bg-white p-10 rounded-lg shadow-2xl">
            <h2 class="text-3xl font-bold text-gray-900 font-playfair">
                You are already logged in
            </h2>
            <p class="mt-2 text-gray-600">
                Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            </p>
            <div class="mt-4 flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 justify-center">
                <a href="index.php" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                    <i class="fas fa-home mr-2"></i> Go to Homepage
                </a>
                <a href="logout.php" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

</body>
</html> 