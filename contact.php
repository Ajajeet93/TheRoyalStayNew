<?php
require_once __DIR__ . '/includes/config.local.php';
require_once __DIR__ . '/includes/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Handle admin actions
if ($is_admin && isset($_POST['action'])) {
    $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
    
    if ($_POST['action'] === 'mark_answered' && $message_id > 0) {
        // Update the message status to "answered"
        // In a real implementation, you would update this in the database
        $success_admin = "Message #" . $message_id . " marked as answered successfully!";
    } elseif ($_POST['action'] === 'delete' && $message_id > 0) {
        // Delete the message
        // In a real implementation, you would delete this from the database
        $success_admin = "Message #" . $message_id . " deleted successfully!";
    }
}

// Handle form submission only if not admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_admin && !isset($_POST['action'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Here you would typically send an email or store the message in the database
    // For now, we'll just show a success message
    $success = "Thank you for your message. We'll get back to you soon!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <?php include 'includes/nav.php'; ?>

    <!-- Contact Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Contact Us</h1>
            
            <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (isset($success_admin)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_admin; ?></span>
            </div>
            <?php endif; ?>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Contact Information -->
                        <?php if (!$is_admin): ?>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Get in Touch</h2>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-map-marker-alt text-indigo-600 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-gray-900">Address</h3>
                                        <p class="mt-1 text-sm text-gray-500">42, Juhu Tara Road<br>Juhu Beach, Mumbai - 400049<br>Maharashtra, India</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-phone text-indigo-600 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-gray-900">Phone</h3>
                                        <p class="mt-1 text-sm text-gray-500">+91 22 2345 6789</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-gray-900">Email</h3>
                                        <p class="mt-1 text-sm text-gray-500">info@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-clock text-indigo-600 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-gray-900">Hours</h3>
                                        <p class="mt-1 text-sm text-gray-500">24/7 Reception</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-map-marker-alt text-indigo-600 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-gray-900">Location</h3>
                                        <p class="mt-1 text-sm text-gray-500">Downtown Area</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Contact Form -->
                        <div class="<?php echo $is_admin ? 'col-span-2' : ''; ?>">
                            <?php if ($is_admin): ?>
                            <!-- Admin Contact Messages Section -->
                            <div class="mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Contact Messages</h3>
                                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                    <ul class="divide-y divide-gray-200">
                                        <li>
                                            <div class="px-4 py-4 sm:px-6">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-medium text-indigo-600 truncate">John Doe</p>
                                                    <div class="ml-2 flex-shrink-0 flex">
                                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            New
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="mt-2 flex justify-between">
                                                    <div class="sm:flex">
                                                        <p class="flex items-center text-sm text-gray-500">
                                                            <i class="fas fa-envelope flex-shrink-0 mr-1.5 text-gray-400"></i>
                                                            johndoe@example.com
                                                        </p>
                                                        <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                                            <i class="fas fa-calendar flex-shrink-0 mr-1.5 text-gray-400"></i>
                                                            April 7, 2025
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-900 font-medium">Room Availability Question</p>
                                                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">I'm interested in booking a deluxe room for 2 adults from May 15-20. Do you have availability?</p>
                                                </div>
                                                <div class="mt-3 flex">
                                                    <button class="inline-flex items-center px-3 py-1 border border-transparent text-xs rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none mr-2">
                                                        <i class="fas fa-reply mr-1"></i> Reply
                                                    </button>
                                                    <form method="POST" class="inline-block mr-2">
                                                        <input type="hidden" name="message_id" value="1">
                                                        <input type="hidden" name="action" value="mark_answered">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                                                            <i class="fas fa-check mr-1"></i> Mark as Answered
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="inline-block">
                                                        <input type="hidden" name="message_id" value="1">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="px-4 py-4 sm:px-6">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-medium text-indigo-600 truncate">Sarah Johnson</p>
                                                    <div class="ml-2 flex-shrink-0 flex">
                                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            Answered
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="mt-2 flex justify-between">
                                                    <div class="sm:flex">
                                                        <p class="flex items-center text-sm text-gray-500">
                                                            <i class="fas fa-envelope flex-shrink-0 mr-1.5 text-gray-400"></i>
                                                            sarah@example.com
                                                        </p>
                                                        <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                                            <i class="fas fa-calendar flex-shrink-0 mr-1.5 text-gray-400"></i>
                                                            April 5, 2025
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-900 font-medium">Special Requests</p>
                                                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">Can you accommodate special dietary requirements? I have gluten allergies.</p>
                                                </div>
                                                <div class="mt-3 flex">
                                                    <button class="inline-flex items-center px-3 py-1 border border-transparent text-xs rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none mr-2">
                                                        <i class="fas fa-reply mr-1"></i> Reply
                                                    </button>
                                                    <form method="POST" class="inline-block mr-2">
                                                        <input type="hidden" name="message_id" value="2">
                                                        <input type="hidden" name="action" value="mark_answered">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                                                            <i class="fas fa-check mr-1"></i> Mark as Answered
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="inline-block">
                                                        <input type="hidden" name="message_id" value="2">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mt-4 text-center">
                                    <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        View All Contact Messages
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                    <input type="text" name="subject" id="subject" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                                    <textarea name="message" id="message" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                                <div>
                                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                        Send Message
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 