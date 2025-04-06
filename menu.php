<?php
require_once 'includes/config.local.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define menu items
$menu_items = [
    'appetizer' => [
        [
            'name' => 'Samosa',
            'description' => 'Crispy pastry filled with spiced potatoes and peas',
            'price' => 4.99,
            'spice_level' => 'medium',
            'is_vegetarian' => true,
            'image_url' => 'images/menu/samosa.jpg'
        ],
        [
            'name' => 'Tandoori Chicken Wings',
            'description' => 'Marinated chicken wings cooked in tandoor',
            'price' => 6.99,
            'spice_level' => 'medium',
            'is_vegetarian' => false,
            'image_url' => 'images/menu/tandoori-wings.jpg'
        ]
    ],
    'main_course' => [
        [
            'name' => 'Butter Chicken',
            'description' => 'Tender chicken in rich tomato cream sauce',
            'price' => 12.99,
            'spice_level' => 'medium',
            'is_vegetarian' => false,
            'image_url' => 'images/menu/butter-chicken.jpg'
        ],
        [
            'name' => 'Paneer Tikka',
            'description' => 'Grilled cottage cheese in spiced yogurt marinade',
            'price' => 11.99,
            'spice_level' => 'medium',
            'is_vegetarian' => true,
            'image_url' => 'images/menu/paneer-tikka.jpg'
        ],
        [
            'name' => 'Lamb Rogan Josh',
            'description' => 'Kashmiri-style lamb curry with aromatic spices',
            'price' => 14.99,
            'spice_level' => 'hot',
            'is_vegetarian' => false,
            'image_url' => 'images/menu/rogan-josh.jpeg'
        ],
        [
            'name' => 'Dal Makhani',
            'description' => 'Creamy black lentils simmered overnight',
            'price' => 10.99,
            'spice_level' => 'mild',
            'is_vegetarian' => true,
            'image_url' => 'images/menu/dal-makhani.jpg'
        ],
        [
            'name' => 'Biryani',
            'description' => 'Fragrant basmati rice with aromatic spices',
            'price' => 13.99,
            'spice_level' => 'medium',
            'is_vegetarian' => false,
            'image_url' => 'images/menu/biryani.jpeg'
        ]
    ],
    'dessert' => [
        [
            'name' => 'Gulab Jamun',
            'description' => 'Sweet milk dumplings in sugar syrup',
            'price' => 3.99,
            'spice_level' => 'mild',
            'is_vegetarian' => true,
            'image_url' => 'images/menu/gulab-jamun.jpeg'
        ],
        [
            'name' => 'Mango Kulfi',
            'description' => 'Traditional Indian ice cream with mango',
            'price' => 4.99,
            'spice_level' => 'mild',
            'is_vegetarian' => true,
            'image_url' => 'images/menu/mango-kulfi.jpg'
        ]
    ],
    'beverage' => [
        [
            'name' => 'Masala Chai',
            'description' => 'Spiced Indian tea with milk',
            'price' => 2.99,
            'spice_level' => 'mild',
            'is_vegetarian' => true,
            'image_url' => 'images/menu/masala-chai.jpg'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - The Royal Lotus</title>
    <link rel="icon" type="image/jpeg" href="images/logo/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/nav.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Restaurant Menu</h1>
            <p class="mt-4 text-lg text-gray-500">Discover our authentic Indian cuisine</p>
        </div>

        <div class="space-y-12">
            <?php foreach ($menu_items as $category => $items): ?>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-900">
                            <?php echo ucwords(str_replace('_', ' ', $category)); ?>
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($items as $item): ?>
                            <div class="p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <img class="h-24 w-24 rounded-lg object-cover" 
                                             src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                                <?php if ($item['is_vegetarian']): ?>
                                                    <span class="ml-2 text-green-600">
                                                        <i class="fas fa-leaf"></i> Vegetarian
                                                    </span>
                                                <?php endif; ?>
                                            </h3>
                                            <p class="text-lg font-bold text-yellow-600">
                                                $<?php echo number_format($item['price'], 2); ?>
                                            </p>
                                        </div>
                                        <p class="mt-1 text-gray-500">
                                            <?php echo htmlspecialchars($item['description']); ?>
                                        </p>
                                        <div class="mt-2 flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php
                                                switch ($item['spice_level']) {
                                                    case 'mild':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'medium':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'hot':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    case 'extra_hot':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                }
                                                ?>">
                                                <?php echo ucfirst($item['spice_level']); ?> Spice Level
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 