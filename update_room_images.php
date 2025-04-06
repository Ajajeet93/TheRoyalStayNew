<?php
require_once 'includes/config.local.php';
require_once 'includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database connection
$db = new Database();
$message = '';
$error = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
    $image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $image_url_2 = isset($_POST['image_url_2']) ? trim($_POST['image_url_2']) : '';
    $image_url_3 = isset($_POST['image_url_3']) ? trim($_POST['image_url_3']) : '';
    
    if ($room_id <= 0) {
        $error = 'Please select a valid room';
    } else {
        try {
            // Update the room images
            $db->query(
                "UPDATE room_types SET image_url = ?, image_url_2 = ?, image_url_3 = ? WHERE id = ?",
                [$image_url, $image_url_2, $image_url_3, $room_id]
            );
            
            $message = 'Room images updated successfully';
        } catch (Exception $e) {
            $error = 'Error updating room images: ' . $e->getMessage();
        }
    }
}

// Get all room types
$rooms = $db->fetchAll("SELECT * FROM room_types ORDER BY name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Room Images - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .preview-image {
            max-height: 150px;
            max-width: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Update Room Images</h1>
        
        <?php if ($message): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $message; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form method="post" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="room_id">
                        Select Room
                    </label>
                    <select name="room_id" id="room_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- Select a Room --</option>
                        <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>">
                            <?php echo htmlspecialchars($room['name']); ?> - $<?php echo number_format($room['price_per_night'], 2); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image_url">
                        Main Image URL
                    </label>
                    <input type="url" name="image_url" id="image_url" placeholder="https://example.com/image.jpg" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-gray-600 text-xs italic mt-1">Enter URL for the main room image</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image_url_2">
                        Second Image URL
                    </label>
                    <input type="url" name="image_url_2" id="image_url_2" placeholder="https://example.com/image2.jpg" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-gray-600 text-xs italic mt-1">Enter URL for the second room image</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image_url_3">
                        Third Image URL
                    </label>
                    <input type="url" name="image_url_3" id="image_url_3" placeholder="https://example.com/image3.jpg" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-gray-600 text-xs italic mt-1">Enter URL for the third room image</p>
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Images
                    </button>
                    <a href="manage_rooms.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Back to Manage Rooms
                    </a>
                </div>
            </form>
        </div>
        
        <div class="mt-8">
            <h2 class="text-2xl font-bold mb-4">Current Room Images</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rooms as $room): ?>
                <div class="bg-white shadow-md rounded-lg p-4">
                    <h3 class="text-lg font-bold mb-2"><?php echo htmlspecialchars($room['name']); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium mb-1">Main Image:</p>
                            <?php if (!empty($room['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="Main image" class="preview-image">
                            <?php else: ?>
                            <p class="text-gray-500 italic">No image</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-sm font-medium mb-1">Second Image:</p>
                            <?php if (!empty($room['image_url_2'])): ?>
                            <img src="<?php echo htmlspecialchars($room['image_url_2']); ?>" alt="Second image" class="preview-image">
                            <?php else: ?>
                            <p class="text-gray-500 italic">No image</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-sm font-medium mb-1">Third Image:</p>
                            <?php if (!empty($room['image_url_3'])): ?>
                            <img src="<?php echo htmlspecialchars($room['image_url_3']); ?>" alt="Third image" class="preview-image">
                            <?php else: ?>
                            <p class="text-gray-500 italic">No image</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="mt-8">
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
                <h4 class="font-bold">Sample Image URLs for Testing:</h4>
                <ul class="list-disc ml-6 mt-2">
                    <li>https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80</li>
                    <li>https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80</li>
                    <li>https://images.unsplash.com/photo-1551776235-dde6c3615a85?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80</li>
                    <li>https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        // Script to auto-fill image URLs when a room is selected
        document.getElementById('room_id').addEventListener('change', function() {
            const roomId = this.value;
            if (roomId) {
                fetch(`api/rooms/view.php?id=${roomId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.room) {
                            document.getElementById('image_url').value = data.room.image_url || '';
                            document.getElementById('image_url_2').value = data.room.image_url_2 || '';
                            document.getElementById('image_url_3').value = data.room.image_url_3 || '';
                        }
                    })
                    .catch(error => console.error('Error fetching room data:', error));
            } else {
                document.getElementById('image_url').value = '';
                document.getElementById('image_url_2').value = '';
                document.getElementById('image_url_3').value = '';
            }
        });
    </script>
</body>
</html> 