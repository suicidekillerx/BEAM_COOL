<?php
require_once 'includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireAuth();

$currentPage = 'categories';
$pageTitle = 'Categories';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new category
                $name = $_POST['name'] ?? '';
                $slug = createSlug($name);
                $description = $_POST['description'] ?? '';
                $requestedSortOrder = (int)($_POST['sort_order'] ?? 0);
                
                $pdo = getDBConnection();
                
                // Smart sort order handling
                $sortOrder = $requestedSortOrder;
                if ($sortOrder > 0) {
                    // Get current maximum sort order
                    $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM categories");
                    $stmt->execute();
                    $result = $stmt->fetch();
                    $maxOrder = $result['max_order'] ?? 0;
                    
                    // If requested order is higher than current max, place at the end
                    if ($sortOrder > $maxOrder) {
                        $sortOrder = $maxOrder + 1;
                        $infoMessage = "Requested order $requestedSortOrder was higher than current max ($maxOrder). Placed at position $sortOrder.";
                    } else {
                        // Check if the requested sort order already exists
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM categories WHERE sort_order = ?");
                        $stmt->execute([$sortOrder]);
                        $result = $stmt->fetch();
                        
                        if ($result['count'] > 0) {
                            // Shift all categories with equal or higher sort order up by 1
                            $stmt = $pdo->prepare("UPDATE categories SET sort_order = sort_order + 1 WHERE sort_order >= ?");
                            $stmt->execute([$sortOrder]);
                        }
                    }
                } else {
                    // If sort order is 0 or not specified, place at the end
                    $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM categories");
                    $stmt->execute();
                    $result = $stmt->fetch();
                    $sortOrder = ($result['max_order'] ?? 0) + 1;
                }
                
                // Handle image upload
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../images/categories/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $fileName = 'category_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            $imagePath = 'images/categories/' . $fileName;
                        } else {
                            $errorMessage = "Failed to upload image.";
                        }
                    } else {
                        $errorMessage = "Invalid file type. Allowed: JPG, PNG, GIF, WEBP";
                    }
                }
                
                if (!isset($errorMessage)) {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, sort_order, image) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $description, $sortOrder, $imagePath]);
                    $successMessage = "Category added successfully with sort order: " . $sortOrder;
                    if (isset($infoMessage)) {
                        $successMessage .= " " . $infoMessage;
                    }
                }
                break;
                
            case 'edit':
                // Edit category
                $id = $_POST['id'] ?? 0;
                $name = $_POST['name'] ?? '';
                $slug = createSlug($name);
                $description = $_POST['description'] ?? '';
                $requestedSortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                
                $pdo = getDBConnection();
                
                // Get current sort order
                $stmt = $pdo->prepare("SELECT sort_order FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $currentCategory = $stmt->fetch();
                $currentSortOrder = $currentCategory['sort_order'] ?? 0;
                
                // Smart sort order handling for edit
                $newSortOrder = $requestedSortOrder;
                if ($newSortOrder != $currentSortOrder) {
                    if ($newSortOrder > 0) {
                        // Get current maximum sort order (excluding current category)
                        $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM categories WHERE id != ?");
                        $stmt->execute([$id]);
                        $result = $stmt->fetch();
                        $maxOrder = $result['max_order'] ?? 0;
                        
                        // If requested order is higher than current max, place at the end
                        if ($newSortOrder > $maxOrder) {
                            $newSortOrder = $maxOrder + 1;
                            $infoMessage = "Requested order $requestedSortOrder was higher than current max ($maxOrder). Placed at position $newSortOrder.";
                            
                            // Shift current category's position down
                            $stmt = $pdo->prepare("UPDATE categories SET sort_order = sort_order - 1 WHERE sort_order > ? AND id != ?");
                            $stmt->execute([$currentSortOrder, $id]);
                        } else {
                            // Check if the new sort order already exists (excluding current category)
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM categories WHERE sort_order = ? AND id != ?");
                            $stmt->execute([$newSortOrder, $id]);
                            $result = $stmt->fetch();
                            
                            if ($result['count'] > 0) {
                                if ($newSortOrder > $currentSortOrder) {
                                    // Moving down: shift categories between current and new position up by 1
                                    $stmt = $pdo->prepare("UPDATE categories SET sort_order = sort_order - 1 WHERE sort_order > ? AND sort_order <= ? AND id != ?");
                                    $stmt->execute([$currentSortOrder, $newSortOrder, $id]);
                                } else {
                                    // Moving up: shift categories between new and current position down by 1
                                    $stmt = $pdo->prepare("UPDATE categories SET sort_order = sort_order + 1 WHERE sort_order >= ? AND sort_order < ? AND id != ?");
                                    $stmt->execute([$newSortOrder, $currentSortOrder, $id]);
                                }
                            }
                        }
                    } else {
                        // If sort order is 0, place at the end
                        $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM categories WHERE id != ?");
                        $stmt->execute([$id]);
                        $result = $stmt->fetch();
                        $newSortOrder = ($result['max_order'] ?? 0) + 1;
                        
                        // Shift current category's position down
                        $stmt = $pdo->prepare("UPDATE categories SET sort_order = sort_order - 1 WHERE sort_order > ? AND id != ?");
                        $stmt->execute([$currentSortOrder, $id]);
                    }
                }
                
                // Handle image upload
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../images/categories/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $fileName = 'category_' . $id . '_' . time() . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            $imagePath = 'images/categories/' . $fileName;
                            
                            // Update database with new image
                            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ?, image = ? WHERE id = ?");
                            $stmt->execute([$name, $slug, $description, $newSortOrder, $isActive, $imagePath, $id]);
                        } else {
                            $errorMessage = "Failed to upload image.";
                        }
                    } else {
                        $errorMessage = "Invalid file type. Allowed: JPG, PNG, GIF, WEBP";
                    }
                } else {
                    // No new image uploaded, update without image
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $description, $newSortOrder, $isActive, $id]);
                }
                
                if (!isset($errorMessage)) {
                    $successMessage = "Category updated successfully! Sort order changed from " . $currentSortOrder . " to " . $newSortOrder;
                    if (isset($infoMessage)) {
                        $successMessage .= " " . $infoMessage;
                    }
                }
                break;
                
            case 'delete':
                // Delete category
                $id = $_POST['id'] ?? 0;
                
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                
                $successMessage = "Category deleted successfully!";
                break;
        }
    }
}

// Get all categories
$categories = getCategories();

// Helper function to create slug
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// Helper function to get next available sort order
function getNextSortOrder() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM categories");
    $stmt->execute();
    $result = $stmt->fetch();
    return ($result['max_order'] ?? 0) + 1;
}

// Helper function to get current maximum sort order
function getCurrentMaxSortOrder() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM categories");
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['max_order'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - Categories</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Categories page specific styles */
        .category-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .modal {
            transition: all 0.3s ease;
        }
        
        .modal.show {
            opacity: 1;
            pointer-events: auto;
        }
        
        .modal-content {
            transform: scale(0.7);
            transition: all 0.3s ease;
        }
        
        .modal.show .modal-content {
            transform: scale(1);
        }
        
        /* Mobile responsive styles */
        @media (max-width: 1023px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
            
            .admin-sidebar.open {
                left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }
        
        /* Mobile-specific styles */
        @media (max-width: 640px) {
            .category-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Include Header -->
            <?php include 'includes/header.php'; ?>
            
        <!-- Content Area -->
        <main class="content-area flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- Success Message -->
                <?php if (isset($successMessage)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Error Message -->
                <?php if (isset($errorMessage)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Info Message -->
                <?php if (isset($infoMessage)): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($infoMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 lg:mb-6 space-y-4 sm:space-y-0">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Categories</h1>
                        <p class="text-sm lg:text-base text-gray-600">Manage your product categories</p>
                    </div>
                    <button onclick="openAddModal()" class="bg-black text-white px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200 flex items-center justify-center space-x-2 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Add Category</span>
                    </button>
                </div>
                
                <!-- Categories Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                    <?php foreach ($categories as $category): ?>
                    <div class="category-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 lg:p-6">
                            <div class="flex items-center justify-between mb-3 lg:mb-4">
                                <h3 class="text-base lg:text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($category['name']); ?></h3>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500">#<?php echo $category['id']; ?></span>
                                    <span class="w-2 h-2 rounded-full <?php echo $category['is_active'] ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                </div>
                            </div>
                            
                            <?php if ($category['image']): ?>
                            <div class="mb-3 lg:mb-4">
                                <img src="../<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="w-full h-24 lg:h-32 object-cover rounded">
                            </div>
                            <?php endif; ?>
                            
                            <p class="text-gray-600 text-xs lg:text-sm mb-3 lg:mb-4"><?php echo htmlspecialchars($category['description'] ?? 'No description'); ?></p>
                            
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs lg:text-sm text-gray-500 mb-3 lg:mb-4 space-y-1 sm:space-y-0">
                                <div class="flex items-center space-x-2">
                                    <span class="bg-gray-100 px-2 py-1 rounded text-xs font-medium">#<?php echo $category['sort_order']; ?></span>
                                    <span class="text-gray-400">Sort Order</span>
                                </div>
                                <span class="text-gray-400">Slug: <?php echo htmlspecialchars($category['slug']); ?></span>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($category)); ?>)" class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded text-xs lg:text-sm hover:bg-gray-200 transition-colors duration-200">
                                    Edit
                                </button>
                                <button onclick="deleteCategory(<?php echo $category['id']; ?>)" class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded text-xs lg:text-sm hover:bg-red-200 transition-colors duration-200">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Empty State -->
                <?php if (empty($categories)): ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No categories yet</h3>
                    <p class="text-gray-600 mb-4">Get started by creating your first category</p>
                    <button onclick="openAddModal()" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors duration-200">
                        Add Category
                    </button>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Add Category Modal -->
    <div id="addModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Add Category</h3>
                    <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" value="<?php echo getNextSortOrder(); ?>" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">
                                Next available: <?php echo getNextSortOrder(); ?>. 
                                Current max: <?php echo getCurrentMaxSortOrder(); ?>. 
                                Higher numbers will be placed at the end automatically.
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                            <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 transition-colors duration-200">
                            Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div id="editModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Category</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" id="edit_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort_order" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">
                                Smart reordering: Other categories will be shifted automatically. 
                                Higher numbers than current max will be placed at the end.
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Image</label>
                            <div id="current_image_container" class="mb-3">
                                <img id="current_image" src="" alt="Current category image" class="w-32 h-32 object-cover rounded border border-gray-300 hidden">
                                <p id="no_image_text" class="text-gray-500 text-sm">No image uploaded</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Update Image</label>
                            <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Active</label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" id="edit_is_active" class="rounded border-gray-300 text-black focus:ring-black">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 transition-colors duration-200">
                            Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Delete Category</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <p class="text-gray-600 mb-6">Are you sure you want to delete this category? This action cannot be undone.</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('open');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('open');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) { // Only on mobile
                    const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
                    const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggle && sidebar && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        sidebarOverlay.classList.remove('open');
                    }
                }
            });
        });
        
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').classList.add('show');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }
        
        function openEditModal(category) {
            document.getElementById('edit_id').value = category.id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_description').value = category.description || '';
            document.getElementById('edit_sort_order').value = category.sort_order;
            document.getElementById('edit_is_active').checked = category.is_active == 1;
            
            // Handle current image display
            const currentImage = document.getElementById('current_image');
            const noImageText = document.getElementById('no_image_text');
            
            if (category.image) {
                currentImage.src = '../' + category.image;
                currentImage.classList.remove('hidden');
                noImageText.classList.add('hidden');
            } else {
                currentImage.classList.add('hidden');
                noImageText.classList.remove('hidden');
            }
            
            document.getElementById('editModal').classList.add('show');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        function deleteCategory(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
        
        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                }
            });
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html> 