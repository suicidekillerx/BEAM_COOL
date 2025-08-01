<?php
session_start();
require_once '../includes/functions.php';

$currentPage = 'collections';
$pageTitle = 'Collections';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new collection
                $name = $_POST['name'] ?? '';
                $slug = createSlug($name);
                $description = $_POST['description'] ?? '';
                $requestedSortOrder = (int)($_POST['sort_order'] ?? 0);
                
                $pdo = getDBConnection();
                
                // Collections don't use sort order anymore - they're displayed in random rotation
                $sortOrder = 0;
                
                // Handle image upload
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../images/collections/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $fileName = 'collection_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            $imagePath = 'images/collections/' . $fileName;
                        } else {
                            $errorMessage = "Failed to upload image.";
                        }
                    } else {
                        $errorMessage = "Invalid file type. Allowed: JPG, PNG, GIF, WEBP";
                    }
                }
                
                if (!isset($errorMessage)) {
                    $secret = isset($_POST['secret']) ? 1 : 0;
                    $stmt = $pdo->prepare("INSERT INTO collections (name, slug, description, sort_order, image, secret) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $description, $sortOrder, $imagePath, $secret]);
                    $successMessage = "Collection added successfully!";
                }
                break;
                
            case 'edit':
                // Edit collection
                $id = $_POST['id'] ?? 0;
                $name = $_POST['name'] ?? '';
                $slug = createSlug($name);
                $description = $_POST['description'] ?? '';
                $requestedSortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                
                $pdo = getDBConnection();
                
                // Collections don't use sort order anymore - they're displayed in random rotation
                $newSortOrder = 0;
                
                // Handle image upload
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../images/collections/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $fileName = 'collection_' . $id . '_' . time() . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            $imagePath = 'images/collections/' . $fileName;
                            
                            // Update database with new image
                            $secret = isset($_POST['secret']) ? 1 : 0;
                            $stmt = $pdo->prepare("UPDATE collections SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ?, image = ?, secret = ? WHERE id = ?");
                            $stmt->execute([$name, $slug, $description, $newSortOrder, $isActive, $imagePath, $secret, $id]);
                        } else {
                            $errorMessage = "Failed to upload image.";
                        }
                    } else {
                        $errorMessage = "Invalid file type. Allowed: JPG, PNG, GIF, WEBP";
                    }
                } else {
                    // No new image uploaded, update without image
                    $secret = isset($_POST['secret']) ? 1 : 0;
                    $stmt = $pdo->prepare("UPDATE collections SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ?, secret = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $description, $newSortOrder, $isActive, $secret, $id]);
                }
                
                if (!isset($errorMessage)) {
                    $successMessage = "Collection updated successfully!";
                }
                break;
                
            case 'delete':
                // Delete collection
                $id = $_POST['id'] ?? 0;
                
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM collections WHERE id = ?");
                $stmt->execute([$id]);
                
                $successMessage = "Collection deleted successfully!";
                break;
        }
    }
}

// Get all collections
$collections = getAllCollectionsForAdmin();

// Helper function to create slug
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - Collections</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Collections page specific styles */
        .collection-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .collection-card:hover {
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
            .collection-card {
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
                        <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Collections</h1>
                        <p class="text-sm lg:text-base text-gray-600">Manage your product collections</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="showSecretCollections" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded" checked>
                                <span class="ml-2 text-sm text-gray-700">Show Secret Collections</span>
                            </label>
                        </div>
                        <button onclick="openAddModal()" class="bg-black text-white px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200 flex items-center justify-center space-x-2 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add Collection</span>
                        </button>
                    </div>
                </div>
                
                <!-- Collections Summary -->
                <?php 
                $totalCollections = count($collections);
                $secretCollections = array_filter($collections, function($c) { return $c['secret'] == 1; });
                $normalCollections = $totalCollections - count($secretCollections);
                ?>
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900"><?php echo $totalCollections; ?></div>
                                <div class="text-sm text-gray-600">Total Collections</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600"><?php echo $normalCollections; ?></div>
                                <div class="text-sm text-gray-600">Normal Collections</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600"><?php echo count($secretCollections); ?></div>
                                <div class="text-sm text-gray-600">Secret Collections</div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Secret collections are hidden from the public shop
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Collections Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                    <?php foreach ($collections as $collection): ?>
                    <div class="collection-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($collection['name']); ?></h3>
                                    <?php if ($collection['secret']): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                            SECRET
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500">#<?php echo $collection['id']; ?></span>
                                    <span class="w-2 h-2 rounded-full <?php echo $collection['is_active'] ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                </div>
                            </div>
                            
                            <?php if ($collection['image']): ?>
                            <div class="mb-4">
                                <img src="../<?php echo htmlspecialchars($collection['image']); ?>" alt="<?php echo htmlspecialchars($collection['name']); ?>" class="w-full h-32 object-cover rounded">
                            </div>
                            <?php endif; ?>
                            
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($collection['description'] ?? 'No description'); ?></p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span class="text-gray-400">Slug: <?php echo htmlspecialchars($collection['slug']); ?></span>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($collection)); ?>)" class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-200 transition-colors duration-200">
                                    Edit
                                </button>
                                <button onclick="deleteCollection(<?php echo $collection['id']; ?>)" class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded text-sm hover:bg-red-200 transition-colors duration-200">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Empty State -->
                <?php if (empty($collections)): ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No collections yet</h3>
                    <p class="text-gray-600 mb-4">Get started by creating your first collection</p>
                    <button onclick="openAddModal()" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors duration-200">
                        Add Collection
                    </button>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Add Collection Modal -->
    <div id="addModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Add Collection</h3>
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
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Collection Name</label>
                            <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"></textarea>
                        </div>
                        

                        
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Collection Image</label>
                            <input type="file" id="image" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Recommended: 400x300px, JPG, PNG, GIF, WEBP</p>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="secret" name="secret" class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                            <label for="secret" class="ml-2 text-sm text-gray-700">Secret Collection</label>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 transition-colors duration-200">
                            Add Collection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Collection Modal -->
    <div id="editModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Collection</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Collection Name</label>
                            <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="edit_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"></textarea>
                        </div>
                        

                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Image</label>
                            <div id="current_image_container" class="mb-2">
                                <img id="current_image" src="" alt="Current image" class="w-32 h-24 object-cover rounded border">
                            </div>
                            <label for="edit_image" class="block text-sm font-medium text-gray-700 mb-1">New Image (optional)</label>
                            <input type="file" id="edit_image" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="edit_is_active" name="is_active" class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                            <label for="edit_is_active" class="ml-2 text-sm text-gray-700">Active</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="edit_secret" name="secret" class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                            <label for="edit_secret" class="ml-2 text-sm text-gray-700">Secret Collection</label>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 transition-colors duration-200">
                            Update Collection
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
                    <h3 class="text-lg font-semibold text-gray-900">Delete Collection</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <p class="text-gray-600 mb-6">Are you sure you want to delete this collection? This action cannot be undone.</p>
                
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_id" name="id">
                    
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                            Delete Collection
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
        
        function openEditModal(collection) {
            document.getElementById('edit_id').value = collection.id;
            document.getElementById('edit_name').value = collection.name;
            document.getElementById('edit_description').value = collection.description || '';
            document.getElementById('edit_is_active').checked = collection.is_active == 1;
            document.getElementById('edit_secret').checked = collection.secret == 1;
            
            // Handle current image
            const currentImage = document.getElementById('current_image');
            const currentImageContainer = document.getElementById('current_image_container');
            
            if (collection.image) {
                currentImage.src = '../' + collection.image;
                currentImageContainer.style.display = 'block';
            } else {
                currentImageContainer.style.display = 'none';
            }
            
            document.getElementById('editModal').classList.add('show');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        function deleteCollection(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
        
        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.remove('show');
                });
            }
        });
        
        // Secret collections filter
        document.addEventListener('DOMContentLoaded', function() {
            const showSecretCheckbox = document.getElementById('showSecretCollections');
            const collectionCards = document.querySelectorAll('.collection-card');
            
            function toggleSecretCollections() {
                const showSecret = showSecretCheckbox.checked;
                
                collectionCards.forEach(card => {
                    const secretBadge = card.querySelector('.bg-purple-100');
                    if (secretBadge) {
                        // This is a secret collection
                        card.style.display = showSecret ? 'block' : 'none';
                    }
                });
            }
            
            if (showSecretCheckbox) {
                showSecretCheckbox.addEventListener('change', toggleSecretCollections);
                // Initial state
                toggleSecretCollections();
            }
        });
    </script>
</body>
</html> 