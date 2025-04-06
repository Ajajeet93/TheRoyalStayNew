<?php
// Site configuration
define('SITE_NAME', 'The Royal Lotus');
define('SITE_URL', 'http://localhost/hotel_management');

// Database configuration
define('DB_HOST', 'localhost:3307');
define('DB_USER', 'root');
define('DB_PASS', 'Ajeet');
define('DB_NAME', 'Hotel');

// Enable debugging
define('DEBUG', false);

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 