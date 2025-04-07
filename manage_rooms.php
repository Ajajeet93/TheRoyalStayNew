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
$message = '';
$error = '';

// Handle room type creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Add new room type
    if ($action === 'add_room_type') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $size = $_POST['size'] ?? '';
        $max_occupancy = $_POST['max_occupancy'] ?? 2;
        $image_url = $_POST['image_url'] ?? '';
        
        // Handle image uploads
        $upload_dir = 'images/rooms/';
        
        // Process primary image upload
        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] == 0) {
            $file_tmp = $_FILES['image_upload']['tmp_name'];
            $file_name = basename($_FILES['image_upload']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Generate unique filename
            $new_file_name = uniqid('room_') . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;
            
            // Check if file is an image
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_types) && move_uploaded_file($file_tmp, $destination)) {
                $image_url = $destination;
            }
        }
        
        // Process amenities
        $amenities = [];
        if (isset($_POST['amenities']) && is_array($_POST['amenities'])) {
            $amenities = $_POST['amenities'];
        }
        
        if (empty($name) || empty($description) || empty($price)) {
            $error = 'Please fill in all required fields';
        } else {
            try {
                $db->insert('room_types', [
                    'name' => $name,
                    'description' => $description,
                    'price_per_night' => $price,
                    'size' => $size,
                    'max_occupancy' => $max_occupancy,
                    'amenities' => json_encode($amenities),
                    'image_url' => $image_url,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                $message = 'Room type added successfully';
            } catch (Exception $e) {
                $error = 'Error adding room type: ' . $e->getMessage();
            }
        }
    }
    
    // Delete room type
    else if ($action === 'delete_room_type' && isset($_POST['room_type_id'])) {
        $room_type_id = $_POST['room_type_id'];
        
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
                
                $message = 'Room type deleted successfully';
            } catch (Exception $e) {
                $error = 'Error deleting room type: ' . $e->getMessage();
            }
        }
    }
    
    // Add individual room
    else if ($action === 'add_room') {
        $room_type_id = $_POST['room_type_id'] ?? '';
        $room_number = $_POST['room_number'] ?? '';
        $floor = $_POST['floor'] ?? '';
        $status = $_POST['status'] ?? 'available';
        
        if (empty($room_type_id) || empty($room_number) || empty($floor)) {
            $error = 'Please fill in all required fields';
        } else {
            try {
                $db->insert('rooms', [
                    'room_type_id' => $room_type_id,
                    'room_number' => $room_number,
                    'floor' => $floor,
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                $message = 'Room added successfully';
            } catch (Exception $e) {
                $error = 'Error adding room: ' . $e->getMessage();
            }
        }
    }
    
    // Delete individual room
    else if ($action === 'delete_room' && isset($_POST['room_id'])) {
        $room_id = $_POST['room_id'];
        
        try {
            $db->query("DELETE FROM rooms WHERE id = ?", [$room_id]);
            $message = 'Room deleted successfully';
        } catch (Exception $e) {
            $error = 'Error deleting room: ' . $e->getMessage();
        }
    }
}

// Get all room types
$room_types = $db->query("SELECT * FROM room_types ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get all individual rooms
$rooms = $db->query(
    "SELECT r.*, rt.name as room_type_name 
     FROM rooms r
     JOIN room_types rt ON r.room_type_id = rt.id
     ORDER BY r.room_type_id, r.room_number ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Common amenities for the add form
$common_amenities = ['WiFi', 'TV', 'Air Conditioning', 'Mini Bar', 'Ocean View', 'Balcony', 'Kitchen', 'Living Room', 'Bathtub'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/nav.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Manage Rooms</h1>
            <a href="admin.php" class="inline-flex items-center px-4 py-2 border-2 border-indigo-600 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-arrow-left mr-2"></i> Back to Admin Panel
            </a>
        </div>
        
        <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h2 class="text-lg font-medium text-gray-900">Room Types</h2>
            </div>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($room_types as $room_type): ?>
                <li>
                    <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($room_type['name']); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($room_type['description']); ?></p>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <span class="mr-4"><i class="fas fa-money-bill-wave mr-1"></i> $<?php echo number_format($room_type['price_per_night'], 2); ?> per night</span>
                                <span class="mr-4"><i class="fas fa-expand-arrows-alt mr-1"></i> <?php echo htmlspecialchars($room_type['size']); ?></span>
                                <span><i class="fas fa-users mr-1"></i> Max <?php echo $room_type['max_occupancy']; ?> guests</span>
                            </div>
                            <?php 
                            $amenities = json_decode($room_type['amenities'], true);
                            if ($amenities && is_array($amenities)): 
                            ?>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <?php foreach ($amenities as $amenity): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo htmlspecialchars($amenity); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this room type? This will also delete all rooms of this type.');">
                            <input type="hidden" name="action" value="delete_room_type">
                            <input type="hidden" name="room_type_id" value="<?php echo $room_type['id']; ?>">
                            <button type="submit" class="inline-flex items-center px-3 py-1 border-2 border-red-600 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                <i class="fas fa-trash-alt mr-1"></i> Delete
                            </button>
                        </form>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Add Room Type Form -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-4 sm:px-6 bg-gradient-to-r from-green-600 to-teal-500 flex justify-between items-center rounded-t-lg">
                    <h2 class="text-lg font-medium text-white">Add New Room Type</h2>
                    <span class="bg-white text-teal-600 rounded-full p-1 shadow">
                        <i class="fas fa-bed text-xl"></i>
                    </span>
                </div>
                <div class="px-6 py-6 sm:p-8 border-t-0 border-2 border-teal-100 rounded-b-lg">
                    <form method="POST" action="" enctype="multipart/form-data" class="space-y-5">
                        <input type="hidden" name="action" value="add_room_type">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Room Type Name <span class="text-red-500">*</span></label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-tag text-gray-400"></i>
                                    </div>
                                    <input type="text" name="name" id="name" class="block w-full border-2 border-gray-300 rounded-md py-2 pl-10 pr-3 bg-white shadow-sm focus:ring-teal-500 focus:border-teal-500" required>
                                </div>
                            </div>
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price per Night <span class="text-red-500">*</span></label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="price" id="price" class="block w-full pl-7 pr-3 py-2 border-2 border-gray-300 rounded-md bg-white shadow-sm focus:ring-teal-500 focus:border-teal-500" required>
                                </div>
                            </div>
                            <div>
                                <label for="size" class="block text-sm font-medium text-gray-700 mb-1">Room Size</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-ruler-combined text-gray-400"></i>
                                    </div>
                                    <input type="text" name="size" id="size" class="block w-full border-2 border-gray-300 rounded-md py-2 pl-10 pr-3 bg-white shadow-sm focus:ring-teal-500 focus:border-teal-500" placeholder="e.g., 40 m²">
                                </div>
                            </div>
                            <div>
                                <label for="max_occupancy" class="block text-sm font-medium text-gray-700 mb-1">Max Occupancy</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400"></i>
                                    </div>
                                    <input type="number" name="max_occupancy" id="max_occupancy" min="1" max="10" class="block w-full border-2 border-gray-300 rounded-md py-2 pl-10 pr-3 bg-white shadow-sm focus:ring-teal-500 focus:border-teal-500" value="2">
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                            <textarea name="description" id="description" rows="3" class="block w-full border-2 border-gray-300 rounded-md py-2 px-3 bg-white shadow-sm focus:ring-teal-500 focus:border-teal-500" required placeholder="Describe the features and amenities of this room type..."></textarea>
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <label class="block text-base font-bold text-gray-700 mb-3">Room Image</label>
                            
                            <div class="flex flex-nowrap overflow-x-auto pb-2 space-x-4 carousel-inputs">
                                <!-- Image 1 -->
                                <div class="min-w-[250px] flex-1">
                                    <div class="bg-white p-3 rounded-lg border border-gray-200 h-full">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Image</label>
                                        <div class="relative border-2 border-gray-300 border-dashed rounded-md mb-2">
                                            <input type="file" name="image_upload" id="image_upload" class="w-full h-full absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                            <div class="text-center p-4">
                                                <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl mb-1"></i>
                                                <p class="text-xs text-gray-500">Upload Image</p>
                                            </div>
                                        </div>
                                        <div class="text-center mb-2">
                                            <span class="text-gray-500 text-sm">OR</span>
                                        </div>
                                        <input type="url" name="image_url" id="image_url" class="w-full border-2 border-gray-300 rounded-md py-2 px-3 bg-white shadow-sm text-sm" placeholder="Image URL">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <?php foreach ($common_amenities as $amenity): ?>
                                <div class="flex items-center">
                                    <input type="checkbox" name="amenities[]" value="<?php echo htmlspecialchars($amenity); ?>" id="amenity-<?php echo str_replace(' ', '-', strtolower($amenity)); ?>" class="h-5 w-5 text-teal-600 border-2 border-gray-300 rounded focus:ring-teal-500">
                                    <label for="amenity-<?php echo str_replace(' ', '-', strtolower($amenity)); ?>" class="ml-2 text-sm text-gray-700"><?php echo htmlspecialchars($amenity); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-200">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-4 border-2 border-indigo-600 text-base font-medium rounded-md shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                <i class="fas fa-plus-circle text-xl mr-2"></i> CREATE ROOM TYPE
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Add Individual Room Form -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-4 sm:px-6 bg-gradient-to-r from-indigo-600 to-blue-500 flex justify-between items-center rounded-t-lg">
                    <h2 class="text-lg font-medium text-white">Add New Room</h2>
                    <span class="bg-white text-indigo-600 rounded-full p-1 shadow">
                        <i class="fas fa-door-open text-xl"></i>
                    </span>
                </div>
                <div class="px-6 py-6 sm:p-8 border-t-0 border-2 border-indigo-100 rounded-b-lg">
                    <form method="POST" class="space-y-5">
                        <input type="hidden" name="action" value="add_room">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="room_type_id" class="block text-sm font-medium text-gray-700 mb-1">Room Type <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="room_type_id" id="room_type_id" required class="block w-full border-2 border-gray-300 rounded-md py-2 pl-3 pr-10 bg-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 appearance-none">
                                        <option value="">Select a room type</option>
                                        <?php foreach ($room_types as $room_type): ?>
                                        <option value="<?php echo $room_type['id']; ?>"><?php echo htmlspecialchars($room_type['name']); ?> - $<?php echo number_format($room_type['price_per_night'], 2); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="room_number" class="block text-sm font-medium text-gray-700 mb-1">Room Number <span class="text-red-500">*</span></label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-hashtag text-gray-400"></i>
                                    </div>
                                    <input type="text" name="room_number" id="room_number" required class="block w-full border-2 border-gray-300 rounded-md py-2 pl-10 pr-3 bg-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 101">
                                </div>
                            </div>
                            
                            <div>
                                <label for="floor" class="block text-sm font-medium text-gray-700 mb-1">Floor <span class="text-red-500">*</span></label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-building text-gray-400"></i>
                                    </div>
                                    <input type="number" name="floor" id="floor" required min="1" class="block w-full border-2 border-gray-300 rounded-md py-2 pl-10 pr-3 bg-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Floor number">
                                </div>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <div class="relative">
                                    <select name="status" id="status" class="block w-full border-2 border-gray-300 rounded-md py-2 pl-3 pr-10 bg-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 appearance-none">
                                        <option value="available">Available</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="occupied">Occupied</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-200">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border-2 border-indigo-600 text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-plus-circle mr-2"></i> Create New Room
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Individual Rooms List -->
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h2 class="text-lg font-medium text-gray-900">Individual Rooms</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Room Number
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Floor
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No rooms found. Add some rooms above.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($room['room_number']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($room['room_type_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $room['floor']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    switch ($room['status']) {
                                        case 'available':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'occupied':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'maintenance':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($room['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this room?');">
                                    <input type="hidden" name="action" value="delete_room">
                                    <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900 border-2 border-red-600 rounded-md px-3 py-1">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 