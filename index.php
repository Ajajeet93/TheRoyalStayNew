<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get featured rooms
$db = new Database();
$featured_rooms = $db->query("SELECT * FROM room_types LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Royal Lotus</title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <script>
        // Initialize mobile menu on page load
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
    <style>
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/nav.php'; ?>

    <!-- Hero Section -->
    <div class="relative">
        <div class="absolute inset-0">
            <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Hotel Background">
            <div class="absolute inset-0 bg-black opacity-50"></div>
        </div>
        <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-white font-playfair sm:text-5xl lg:text-6xl">Welcome to The Royal Lotus</h1>
            <p class="mt-6 text-xl text-white max-w-3xl">Experience luxury and comfort in the heart of the city. Book your stay with us and enjoy world-class amenities.</p>
            <div class="mt-10">
                <a href="booking.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                    Book Now
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Rooms -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-8 text-center">Featured Rooms</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Standard Room -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Standard Room" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Standard Room</h3>
                        <p class="mt-2 text-gray-600">Comfortable room with essential amenities</p>
                        <div class="mt-4">
                            <span class="text-2xl font-bold text-yellow-600">$7500.00</span>
                            <span class="text-sm text-gray-500">per night</span>
                        </div>
                        <a href="booking.php?room_type=1" class="mt-4 block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                            Book Now
                        </a>
                    </div>
                </div>

                <!-- Deluxe Room -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Deluxe Room" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Deluxe Room</h3>
                        <p class="mt-2 text-gray-600">Spacious room with premium amenities</p>
                        <div class="mt-4">
                            <span class="text-2xl font-bold text-yellow-600">$11,250.00</span>
                            <span class="text-sm text-gray-500">per night</span>
                        </div>
                        <a href="booking.php?room_type=2" class="mt-4 block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                            Book Now
                        </a>
                    </div>
                </div>

                <!-- Suite -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="images/rooms/suite-2.jpeg" alt="Suite" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Suite</h3>
                        <p class="mt-2 text-gray-600">Luxury suite with separate living area</p>
                        <div class="mt-4">
                            <span class="text-2xl font-bold text-yellow-600">$18,750.00</span>
                            <span class="text-sm text-gray-500">per night</span>
                        </div>
                        <a href="booking.php?room_type=3" class="mt-4 block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                            Book Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Process Section -->
    <section class="py-16 bg-yellow-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-2 text-center font-playfair">How Booking Works</h2>
            <p class="text-center text-gray-600 mb-12 max-w-3xl mx-auto">Our simple 3-step booking process ensures a seamless experience for our guests</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden text-center p-6">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 text-yellow-600 text-xl font-bold mb-4">1</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Book Online</h3>
                    <p class="text-gray-600">Choose your room type, dates, and provide your details to submit a booking request.</p>
                    <div class="mt-4 flex justify-center">
                        <i class="fas fa-laptop text-4xl text-yellow-400"></i>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden text-center p-6">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 text-yellow-600 text-xl font-bold mb-4">2</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Confirmation</h3>
                    <p class="text-gray-600">Our team will review your booking request and confirm availability within 24 hours.</p>
                    <div class="mt-4 flex justify-center">
                        <i class="fas fa-clipboard-check text-4xl text-yellow-400"></i>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden text-center p-6">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 text-yellow-600 text-xl font-bold mb-4">3</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Enjoy Your Stay</h3>
                    <p class="text-gray-600">Once confirmed, your reservation is guaranteed. Arrive and enjoy your luxury stay with us.</p>
                    <div class="mt-4 flex justify-center">
                        <i class="fas fa-glass-cheers text-4xl text-yellow-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Famous Dishes Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-8 text-center">Our Famous Indian Dishes</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="images/menu/butter-chicken.jpg" alt="Butter Chicken" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Butter Chicken</h3>
                        <p class="mt-2 text-gray-600">Tender chicken in rich tomato cream sauce</p>
                        <div class="mt-4">
                            <span class="text-2xl font-bold text-yellow-600">$12.99</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Hyderabadi Biryani" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Hyderabadi Biryani</h3>
                        <p class="mt-2 text-gray-600">Fragrant basmati rice with aromatic spices</p>
                        <div class="mt-4">
                            <span class="text-2xl font-bold text-yellow-600">$13.99</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1631452180519-c014fe946bc7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Butter Paneer" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Butter Paneer</h3>
                        <p class="mt-2 text-gray-600">Cottage cheese in rich tomato cream sauce</p>
                        <div class="mt-4">
                            <span class="text-2xl font-bold text-yellow-600">$11.99</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-base text-yellow-600 font-semibold tracking-wide uppercase">Features</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Why Choose The Royal Lotus?
                </p>
            </div>

            <div class="mt-10">
                <div class="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10">
                    <!-- Feature 1 -->
                    <div class="relative">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                            <i class="fas fa-bed"></i>
                        </div>
                        <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Luxury Rooms</p>
                        <p class="mt-2 ml-16 text-base text-gray-500">
                            Experience ultimate comfort in our elegantly appointed rooms with premium amenities.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="relative">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Fine Dining</p>
                        <p class="mt-2 ml-16 text-base text-gray-500">
                            Savor exquisite cuisine prepared by our world-class chefs in our restaurants.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="relative">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                            <i class="fas fa-spa"></i>
                        </div>
                        <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Spa & Wellness</p>
                        <p class="mt-2 ml-16 text-base text-gray-500">
                            Rejuvenate your senses with our luxurious spa treatments and wellness programs.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="relative">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <p class="ml-16 text-lg leading-6 font-medium text-gray-900">24/7 Service</p>
                        <p class="mt-2 ml-16 text-base text-gray-500">
                            Our dedicated staff is always at your service to ensure a memorable stay.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="relative">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                            <i class="fas fa-swimming-pool"></i>
                        </div>
                        <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Swimming Pool</p>
                        <p class="mt-2 ml-16 text-base text-gray-500">
                            Relax and enjoy our outdoor swimming pool.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-yellow-700">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
            <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                <span class="block">Ready to experience luxury?</span>
                <span class="block text-yellow-200">Book your stay today.</span>
            </h2>
            <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                <div class="inline-flex rounded-md shadow">
                    <a href="booking.php" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-yellow-600 bg-white hover:bg-yellow-50">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 