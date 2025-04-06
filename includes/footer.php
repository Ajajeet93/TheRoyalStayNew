<footer class="bg-gradient-to-br from-gray-900 to-gray-800 pt-8 pb-6">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Logo and Newsletter -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-6 border-b border-gray-700">
            <div class="mb-6 md:mb-0">
                <a href="index.php" class="inline-flex items-center">
                    <span class="text-xl font-bold text-white font-playfair"><?php echo SITE_NAME; ?></span>
                </a>
                <p class="mt-1 text-sm text-gray-400 max-w-xs">Experience luxury and comfort in the heart of the city.</p>
            </div>
            <div class="w-full md:w-auto">
                <h4 class="text-white text-xs uppercase font-semibold tracking-wider mb-2">Subscribe to our newsletter</h4>
                <form class="flex">
                    <input type="email" placeholder="Your email address" class="bg-gray-800 rounded-l-md border-gray-700 text-gray-300 py-1.5 px-3 focus:outline-none focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 min-w-0 flex-1 text-sm">
                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-1.5 px-3 rounded-r-md transition-colors duration-200 text-sm">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <h4 class="text-white text-xs uppercase font-semibold tracking-wider mb-3">Contact</h4>
                <ul class="space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt text-yellow-500 mt-1 mr-2 text-sm"></i>
                        <span class="text-gray-400 text-sm">42, Juhu Tara Road<br>Juhu Beach, Mumbai - 400049</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-phone-alt text-yellow-500 mr-2 text-sm"></i>
                        <span class="text-gray-400 text-sm">+91 22 2345 6789</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-envelope text-yellow-500 mr-2 text-sm"></i>
                        <span class="text-gray-400 text-sm">info@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</span>
                    </li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-white text-xs uppercase font-semibold tracking-wider mb-3">Quick Links</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <ul class="space-y-1">
                            <li>
                                <a href="index.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> Home
                                </a>
                            </li>
                            <li>
                                <a href="rooms.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> Rooms
                                </a>
                            </li>
                            <li>
                                <a href="booking.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> Book Now
                                </a>
                            </li>
                            <li>
                                <a href="contact.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> Contact
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <ul class="space-y-1">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li>
                                    <a href="my-bookings.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                        <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> My Bookings
                                    </a>
                                </li>
                                <li>
                                    <a href="profile.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                        <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <a href="logout.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                        <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> Logout
                                    </a>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a href="login.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                        <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> Login
                                    </a>
                                </li>
                                <li>
                                    <a href="register.php" class="text-sm text-gray-400 hover:text-yellow-500 transition-colors duration-200 flex items-center">
                                        <i class="fas fa-chevron-right text-xs mr-1 text-yellow-600"></i> Register
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="text-white text-xs uppercase font-semibold tracking-wider mb-3">Connect With Us</h4>
                <div class="flex space-x-3 mb-3">
                    <a href="#" class="bg-gray-800 hover:bg-gray-700 text-yellow-500 w-8 h-8 rounded-full flex items-center justify-center transition-colors duration-200">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                    <a href="#" class="bg-gray-800 hover:bg-gray-700 text-yellow-500 w-8 h-8 rounded-full flex items-center justify-center transition-colors duration-200">
                        <i class="fab fa-twitter text-sm"></i>
                    </a>
                    <a href="#" class="bg-gray-800 hover:bg-gray-700 text-yellow-500 w-8 h-8 rounded-full flex items-center justify-center transition-colors duration-200">
                        <i class="fab fa-instagram text-sm"></i>
                    </a>
                    <a href="#" class="bg-gray-800 hover:bg-gray-700 text-yellow-500 w-8 h-8 rounded-full flex items-center justify-center transition-colors duration-200">
                        <i class="fab fa-tripadvisor text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-xs text-gray-400">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <div class="mt-2 md:mt-0 flex space-x-4">
                    <a href="#" class="text-xs text-gray-400 hover:text-yellow-500">Privacy</a>
                    <a href="#" class="text-xs text-gray-400 hover:text-yellow-500">Terms</a>
                    <a href="#" class="text-xs text-gray-400 hover:text-yellow-500">Cookies</a>
                </div>
            </div>
        </div>
    </div>
</footer> 