<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();

// Fetch room types from database
$room_types = $db->query("SELECT * FROM room_types ORDER BY price_per_night ASC")->fetchAll(PDO::FETCH_ASSOC);

// Define default room images if not specified in the database
$default_images = [
    'standard' => 'images/rooms/standard1.jpg',
    'deluxe' => 'images/rooms/deluxe-room-1.jpg',
    'suite' => 'images/rooms/suite-1.jpg',
    'basic' => 'images/rooms/basic-room.jpg',
    'default' => 'images/rooms/room-default.jpg'
];

// Helper function to get appropriate image for room type
function getRoomImage($room_type) {
    global $default_images;
    
    // If room has image_url, use that
    if (!empty($room_type['image_url'])) {
        return $room_type['image_url'];
    }
    
    // Otherwise find appropriate default image based on room type name
    $name = strtolower($room_type['name']);
    foreach ($default_images as $key => $image) {
        if (strpos($name, $key) !== false) {
            return $image;
        }
    }
    
    // If no match, return default image
    return $default_images['default'];
}

// Check if user is admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Handle room deletion if admin
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_room_type') {
    $room_type_id = $_POST['room_type_id'] ?? 0;
    
    // Check if there are active bookings for this room type
    $active_bookings = $db->fetch(
        "SELECT COUNT(*) as count FROM bookings WHERE room_type_id = ? AND status = 'confirmed'", 
        [$room_type_id]
    );
    
    if ($active_bookings && $active_bookings['count'] > 0) {
        $error = 'Cannot delete room type with active bookings';
    } else {
        try {
            // First delete any pending bookings
            $db->query("DELETE FROM bookings WHERE room_type_id = ?", [$room_type_id]);
            
            // Then delete any rooms of this type
            $db->query("DELETE FROM rooms WHERE room_type_id = ?", [$room_type_id]);
            
            // Finally delete the room type
            $db->query("DELETE FROM room_types WHERE id = ?", [$room_type_id]);
            
            // Redirect to refresh the page
            header('Location: rooms.php');
            exit();
        } catch (Exception $e) {
            $error = 'Error deleting room type: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/nav.php'; ?>

    <!-- Room Types Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Our Room Types</h1>
            
            <?php if ($is_admin): ?>
            <a href="manage_rooms.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                <i class="fas fa-cog mr-2"></i> Manage Rooms
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Room Comparison Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($room_types as $room_type): 
                // Get amenities
                $amenities = json_decode($room_type['amenities'], true) ?: [];
                
                // Get room images or use default
                $images = [];
                
                // Primary image
                if (!empty($room_type['image_url'])) {
                    $images[] = $room_type['image_url'];
                }
                
                // Second image
                if (!empty($room_type['image_url_2'])) {
                    $images[] = $room_type['image_url_2'];
                }
                
                // Third image
                if (!empty($room_type['image_url_3'])) {
                    $images[] = $room_type['image_url_3'];
                }
                
                // If no images are defined, use default
                if (empty($images)) {
                    $images[] = 'images/rooms/deluxe-room-1.jpg';
                }
            ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Tailwind Carousel -->
                <div id="carousel-<?php echo $room_type['id']; ?>" class="relative w-full h-48 overflow-hidden">
                    <!-- Carousel Items -->
                    <div class="flex transition-transform duration-500 ease-in-out transform translate-x-0 h-full" id="carousel-items-<?php echo $room_type['id']; ?>">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="flex-shrink-0 w-full h-full">
                            <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($room_type['name']); ?> - Image <?php echo $index + 1; ?>" class="w-full h-full object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Controls -->
                    <?php if (count($images) > 1): ?>
                    <button 
                        class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full w-10 h-10 flex items-center justify-center focus:outline-none hover:bg-opacity-75"
                        onclick="moveCarousel(<?php echo $room_type['id']; ?>, -1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button 
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full w-10 h-10 flex items-center justify-center focus:outline-none hover:bg-opacity-75"
                        onclick="moveCarousel(<?php echo $room_type['id']; ?>, 1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <!-- Indicators -->
                    <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
                        <?php for ($i = 0; $i < count($images); $i++): ?>
                        <button 
                            class="w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100 <?php echo $i === 0 ? 'bg-opacity-100' : ''; ?>" 
                            onclick="goToSlide(<?php echo $room_type['id']; ?>, <?php echo $i; ?>)">
                        </button>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <h3 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($room_type['name']); ?></h3>
                        
                        <?php if ($is_admin): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this room type? This will also delete all rooms of this type.');">
                            <input type="hidden" name="action" value="delete_room_type">
                            <input type="hidden" name="room_type_id" value="<?php echo $room_type['id']; ?>">
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                    
                    <p class="mt-2 text-gray-600"><?php echo htmlspecialchars($room_type['description']); ?></p>
                    <div class="mt-4">
                        <h4 class="text-lg font-medium text-gray-900">Amenities:</h4>
                        <ul class="mt-2 space-y-1">
                            <?php foreach ($amenities as $amenity): ?>
                            <li class="flex items-center text-gray-600">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <?php echo htmlspecialchars($amenity); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="mt-4 flex items-center space-x-4 text-sm text-gray-500">
                        <div><i class="fas fa-ruler-combined mr-1"></i> <?php echo htmlspecialchars($room_type['size']); ?></div>
                        <div><i class="fas fa-users mr-1"></i> Max <?php echo $room_type['max_occupancy']; ?> guests</div>
                    </div>
                    <div class="mt-6">
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-yellow-600">$<?php echo number_format($room_type['price_per_night'], 2); ?></span>
                            <span class="text-sm text-gray-500">per night</span>
                        </div>
                        <a href="booking.php?room_type=<?php echo $room_type['id']; ?>" class="mt-4 block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                            Book Now
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if ($is_admin): ?>
            <!-- Add Room Card (Admin Only) -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 border-dashed border-gray-300 flex flex-col items-center justify-center p-6">
                <div class="text-center">
                    <i class="fas fa-plus-circle text-4xl text-yellow-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Add New Room Type</h3>
                    <p class="text-gray-600 mb-4">Create a new room type for your hotel</p>
                    <a href="manage_rooms.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                        <i class="fas fa-plus mr-2"></i> Add Room Type
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Tailwind Carousel functionality
        const carouselStates = {};
        
        function initializeCarousel(roomId) {
            const carouselItems = document.getElementById(`carousel-items-${roomId}`);
            if (!carouselItems) return;
            
            const totalSlides = carouselItems.children.length;
            
            carouselStates[roomId] = {
                currentSlide: 0,
                totalSlides: totalSlides
            };
        }
        
        function moveCarousel(roomId, direction) {
            if (!carouselStates[roomId]) {
                initializeCarousel(roomId);
            }
            
            const state = carouselStates[roomId];
            const carouselItems = document.getElementById(`carousel-items-${roomId}`);
            const indicators = document.querySelectorAll(`#carousel-${roomId} .absolute.bottom-2 button`);
            
            if (state.totalSlides <= 1) return;
            
            // Update current slide
            state.currentSlide = (state.currentSlide + direction + state.totalSlides) % state.totalSlides;
            
            // Move carousel
            carouselItems.style.transform = `translateX(-${state.currentSlide * 100}%)`;
            
            // Update indicators
            indicators.forEach((indicator, index) => {
                if (index === state.currentSlide) {
                    indicator.classList.add('bg-opacity-100');
                } else {
                    indicator.classList.remove('bg-opacity-100');
                }
            });
        }
        
        function goToSlide(roomId, slideIndex) {
            if (!carouselStates[roomId]) {
                initializeCarousel(roomId);
            }
            
            const state = carouselStates[roomId];
            const carouselItems = document.getElementById(`carousel-items-${roomId}`);
            const indicators = document.querySelectorAll(`#carousel-${roomId} .absolute.bottom-2 button`);
            
            // Update current slide
            state.currentSlide = slideIndex;
            
            // Move carousel
            carouselItems.style.transform = `translateX(-${state.currentSlide * 100}%)`;
            
            // Update indicators
            indicators.forEach((indicator, index) => {
                if (index === state.currentSlide) {
                    indicator.classList.add('bg-opacity-100');
                } else {
                    indicator.classList.remove('bg-opacity-100');
                }
            });
        }
        
        // Initialize all carousels on page load
        document.addEventListener('DOMContentLoaded', () => {
            <?php foreach ($room_types as $room_type): ?>
            initializeCarousel(<?php echo $room_type['id']; ?>);
            <?php endforeach; ?>
        });
    </script>
</body>
</html> 