<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user name if logged in
$user_name = 'Profile';
if (isset($_SESSION['user_id'])) {
    require_once 'db.php';
    $db = new Database();
    $user = $db->query("SELECT name FROM users WHERE id = ?", [$_SESSION['user_id']])->fetch(PDO::FETCH_ASSOC);
    if ($user && !empty($user['name'])) {
        $user_name = $user['name'];
    }
}

// Check if user is admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-20">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center space-x-4">
                    <a href="index.php" class="flex items-center">
                        <img src="images/logo/logo.jpg" alt="<?php echo SITE_NAME; ?>" class="h-12 w-12 rounded-full object-cover border-2 border-gray-200">
                    </a>
                    <div class="flex flex-col">
                        <a href="index.php" class="text-gray-800 text-xl font-bold font-playfair hidden sm:block">The Royal Lotus</a>
                        <span class="text-gray-600 text-sm hidden sm:block">PREMIUM HOTEL</span>
                    </div>
                </div>
            </div>
            <div class="hidden md:flex items-center space-x-4">
                <a href="index.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                
                <?php if ($is_admin): ?>
                <a href="manage_rooms.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Manage Rooms</a>
                <a href="rooms.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Preview Rooms</a>
                <?php else: ?>
                <a href="rooms.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Rooms</a>
                <?php endif; ?>
                
                <a href="menu.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Menu</a>
                
                <?php if ($is_admin): ?>
                <a href="contact.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Message</a>
                <?php else: ?>
                <a href="contact.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <a href="admin.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-tasks mr-1"></i>Show Bookings
                        </a>
                    <?php else: ?>
                        <a href="my-bookings.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">My Bookings</a>
                    <?php endif; ?>
                    <a href="profile.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($user_name); ?>
                    </a>
                    <a href="logout.php" class="text-red-600 hover:text-white hover:bg-red-600 px-3 py-2 rounded-md text-sm font-medium border border-red-600 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-800 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="register.php" class="bg-yellow-600 text-white hover:bg-yellow-700 px-4 py-2 rounded-md text-sm font-medium">Register</a>
                <?php endif; ?>
            </div>
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-800 hover:text-yellow-600">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="index.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Home</a>
            
            <?php if ($is_admin): ?>
            <a href="manage_rooms.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Manage Rooms</a>
            <a href="rooms.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Preview Rooms</a>
            <?php else: ?>
            <a href="rooms.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Rooms</a>
            <?php endif; ?>
            
            <a href="menu.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Menu</a>
            
            <?php if ($is_admin): ?>
            <a href="contact.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Message</a>
            <?php else: ?>
            <a href="contact.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Contact</a>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <a href="admin.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-tasks mr-1"></i>Show Bookings
                    </a>
                <?php else: ?>
                    <a href="my-bookings.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">My Bookings</a>
                <?php endif; ?>
                <a href="profile.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($user_name); ?>
                </a>
                <a href="logout.php" class="text-red-600 hover:text-white hover:bg-red-600 block px-3 py-2 rounded-md text-base font-medium border border-red-600 transition-colors duration-200">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Login</a>
                <a href="register.php" class="text-gray-800 hover:text-yellow-600 block px-3 py-2 rounded-md text-base font-medium">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav> 