<?php
session_start();
require_once '../includes/functions.php';

// Check admin authentication
//require_admin_auth();

$currentPage = 'promo_codes';
$pageTitle = 'Promo Codes';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $pdo = getDBConnection();
    
    try {
        switch ($action) {
            case 'create':
                $data = [
                    'code' => $_POST['code'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'type' => $_POST['type'],
                    'value' => $_POST['value'],
                    'min_order_amount' => $_POST['min_order_amount'],
                    'max_discount' => $_POST['max_discount'] ?: null,
                    'usage_limit' => $_POST['usage_limit'] ?: null,
                    'user_limit' => $_POST['user_limit'] ?: null,
                    'applies_to' => $_POST['applies_to'],
                    'category_ids' => $_POST['category_ids'] ?: null,
                    'product_ids' => $_POST['product_ids'] ?: null,
                    'excluded_categories' => $_POST['excluded_categories'] ?: null,
                    'excluded_products' => $_POST['excluded_products'] ?: null,
                    'start_date' => $_POST['start_date'] ?: null,
                    'end_date' => $_POST['end_date'] ?: null,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'is_first_time_only' => isset($_POST['is_first_time_only']) ? 1 : 0,
                    'is_single_use' => isset($_POST['is_single_use']) ? 1 : 0
                ];
                
                if (createPromoCode($data)) {
                    echo json_encode(['success' => true, 'message' => 'Promo code created successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create promo code']);
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $data = [
                    'code' => $_POST['code'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'type' => $_POST['type'],
                    'value' => $_POST['value'],
                    'min_order_amount' => $_POST['min_order_amount'],
                    'max_discount' => $_POST['max_discount'] ?: null,
                    'usage_limit' => $_POST['usage_limit'] ?: null,
                    'user_limit' => $_POST['user_limit'] ?: null,
                    'applies_to' => $_POST['applies_to'],
                    'category_ids' => $_POST['category_ids'] ?: null,
                    'product_ids' => $_POST['product_ids'] ?: null,
                    'excluded_categories' => $_POST['excluded_categories'] ?: null,
                    'excluded_products' => $_POST['excluded_products'] ?: null,
                    'start_date' => $_POST['start_date'] ?: null,
                    'end_date' => $_POST['end_date'] ?: null,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'is_first_time_only' => isset($_POST['is_first_time_only']) ? 1 : 0,
                    'is_single_use' => isset($_POST['is_single_use']) ? 1 : 0
                ];
                
                if (updatePromoCode($id, $data)) {
                    echo json_encode(['success' => true, 'message' => 'Promo code updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update promo code']);
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                if (deletePromoCode($id)) {
                    echo json_encode(['success' => true, 'message' => 'Promo code deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete promo code']);
                }
                break;
                
            case 'toggle_active':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE promo_codes SET is_active = NOT is_active WHERE id = ?");
                if ($stmt->execute([$id])) {
                    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Get all promo codes
$promoCodes = getAllPromoCodes();
$categories = getCategories();
$products = getProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - Promo Codes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Admin specific styles */
        .admin-sidebar {
            background: linear-gradient(180deg, #000000 0%, #1a1a1a 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .menu-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .menu-item:hover::before {
            left: 100%;
        }
        
        .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #ffffff;
        }
        
        .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }
        
        .menu-icon {
            transition: all 0.3s ease;
        }
        
        .menu-item:hover .menu-icon {
            transform: scale(1.1);
        }
        
        .admin-header {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-toggle {
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            transform: rotate(180deg);
        }
        
        /* Mobile sidebar styles */
        @media (max-width: 1023px) {
            .admin-sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease-in-out;
            }
            
            .admin-sidebar.open {
                left: 0;
            }
            
            #sidebarOverlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease-in-out;
            }
            
            #sidebarOverlay.open {
                opacity: 1;
                visibility: visible;
            }
        }
        
        /* Mobile responsive table */
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .mobile-card {
                display: block;
            }
            
            .mobile-card .card-header {
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem;
            }
            
            .mobile-card .card-body {
                padding: 1rem;
            }
            
            .mobile-card .card-footer {
                border-top: 1px solid #e5e7eb;
                padding: 1rem;
            }
        }
        
        /* Hide desktop table on mobile */
        @media (max-width: 768px) {
            .desktop-table {
                display: none;
            }
        }
        
        /* Show mobile cards on mobile */
        @media (max-width: 768px) {
            .mobile-cards {
                display: block;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-cards {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="lg:hidden"></div>
    
    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'includes/header.php'; ?>
            <main class="content-area flex-1 overflow-y-auto p-4 lg:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h1 class="text-2xl lg:text-3xl font-bold">Promo Codes</h1>
                    <button onclick="openCreateModal()" class="w-full sm:w-auto bg-black text-white px-4 lg:px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors text-sm lg:text-base">
                        <i class="fas fa-plus mr-2"></i>Create Promo Code
                    </button>
                </div>
                
                <!-- Desktop Table View -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden desktop-table">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($promoCodes as $promo): ?>
                                <tr data-promo-id="<?= $promo['id'] ?>">
                                    <td class="px-6 py-4 whitespace-nowrap code-cell">
                                        <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($promo['code']) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap name-cell">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($promo['name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($promo['description']) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap type-cell">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Percentage
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 value-cell">
                                        <?= $promo['value'] ?>%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= $promo['used_count'] ?>/<?= $promo['usage_limit'] ?: '∞' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap status-cell">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full status-badge 
                                            <?= $promo['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $promo['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="editPromoCode(<?= $promo['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button onclick="togglePromoCode(<?= $promo['id'] ?>)" class="text-yellow-600 hover:text-yellow-900 mr-3 toggle-btn">
                                            <?= $promo['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                        <button onclick="deletePromoCode(<?= $promo['id'] ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Mobile Cards View -->
                <div class="mobile-cards space-y-4">
                    <?php foreach ($promoCodes as $promo): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mobile-card">
                        <div class="card-header bg-gray-50 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="font-mono text-sm bg-gray-100 px-3 py-1 rounded-full font-semibold">
                                        <?= htmlspecialchars($promo['code']) ?>
                                    </span>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Percentage
                                    </span>
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full status-badge 
                                    <?= $promo['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $promo['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body px-4 py-3">
                            <div class="space-y-2">
                                <div>
                                    <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($promo['name']) ?></h3>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($promo['description']) ?></p>
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Value:</span>
                                    <span class="font-semibold">
                                        <?= $promo['value'] ?>%
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Usage:</span>
                                    <span class="font-semibold"><?= $promo['used_count'] ?>/<?= $promo['usage_limit'] ?: '∞' ?></span>
                                </div>
                                
                                <?php if ($promo['min_order_amount'] > 0): ?>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Min Order:</span>
                                    <span class="font-semibold"><?= formatPrice($promo['min_order_amount']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-footer px-4 py-3 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex space-x-2">
                                    <button onclick="editPromoCode(<?= $promo['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        Edit
                                    </button>
                                    <button onclick="togglePromoCode(<?= $promo['id'] ?>)" 
                                            class="text-yellow-600 hover:text-yellow-900 text-sm font-medium toggle-btn">
                                        <?= $promo['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </div>
                                <button onclick="deletePromoCode(<?= $promo['id'] ?>)" 
                                        class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Create/Edit Modal -->
    <div id="promoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900 mb-4">Create Promo Code</h3>
                <form id="promoForm" class="space-y-4">
                    <input type="hidden" id="promoId" name="id">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                            <input type="text" id="code" name="code" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <input type="text" id="type" name="type" value="percentage" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-sm cursor-not-allowed">
                            <p class="text-xs text-gray-500 mt-1">Only percentage discounts are supported</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                            <input type="number" id="value" name="value" step="0.001" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Amount</label>
                            <input type="number" id="min_order_amount" name="min_order_amount" step="0.001" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Discount</label>
                            <input type="number" id="max_discount" name="max_discount" step="0.001" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
                            <input type="number" id="usage_limit" name="usage_limit" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="datetime-local" id="start_date" name="start_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="datetime-local" id="end_date" name="end_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" class="mr-2">
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" id="is_first_time_only" name="is_first_time_only" class="mr-2">
                            <span class="text-sm text-gray-700">First Time Only</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" id="is_single_use" name="is_single_use" class="mr-2">
                            <span class="text-sm text-gray-700">Single Use</span>
                        </label>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                        <button type="button" onclick="closeModal()" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 text-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    let isEditMode = false;
    let currentPromoId = null;
    
    function openCreateModal() {
        isEditMode = false;
        currentPromoId = null;
        document.getElementById('modalTitle').textContent = 'Create Promo Code';
        document.getElementById('promoForm').reset();
        document.getElementById('promoModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('promoModal').classList.add('hidden');
    }
    
    function editPromoCode(id) {
        // Fetch promo code data and populate form
        fetch(`get_promo_code.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const promo = data.promo_code;
                    isEditMode = true;
                    currentPromoId = id;
                    
                    document.getElementById('modalTitle').textContent = 'Edit Promo Code';
                    document.getElementById('promoId').value = promo.id;
                    document.getElementById('code').value = promo.code;
                    document.getElementById('name').value = promo.name;
                    document.getElementById('description').value = promo.description;
                    // Type is always percentage, so we don't need to set it
                    // document.getElementById('type').value = promo.type;
                    document.getElementById('value').value = promo.value;
                    document.getElementById('min_order_amount').value = promo.min_order_amount;
                    document.getElementById('max_discount').value = promo.max_discount;
                    document.getElementById('usage_limit').value = promo.usage_limit;
                    document.getElementById('start_date').value = promo.start_date;
                    document.getElementById('end_date').value = promo.end_date;
                    document.getElementById('is_active').checked = promo.is_active == 1;
                    document.getElementById('is_first_time_only').checked = promo.is_first_time_only == 1;
                    document.getElementById('is_single_use').checked = promo.is_single_use == 1;
                    
                    document.getElementById('promoModal').classList.remove('hidden');
                }
            });
    }
    
    function deletePromoCode(id) {
        if (confirm('Are you sure you want to delete this promo code?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('promo_codes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    const row = document.querySelector(`tr[data-promo-id="${id}"]`);
                    if (row) {
                        row.remove();
                    }
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            });
        }
    }
    
    function togglePromoCode(id) {
        const formData = new FormData();
        formData.append('action', 'toggle_active');
        formData.append('id', id);
        
        fetch('promo_codes.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the status in the table
                const statusCell = document.querySelector(`tr[data-promo-id="${id}"] .status-badge`);
                const toggleButton = document.querySelector(`tr[data-promo-id="${id}"] .toggle-btn`);
                
                if (statusCell && toggleButton) {
                    const isActive = statusCell.textContent.trim() === 'Active';
                    statusCell.textContent = isActive ? 'Inactive' : 'Active';
                    statusCell.className = `inline-flex px-2 py-1 text-xs font-semibold rounded-full status-badge ${isActive ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}`;
                    toggleButton.textContent = isActive ? 'Activate' : 'Deactivate';
                }
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        });
    }
    
    document.getElementById('promoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', isEditMode ? 'update' : 'create');
        
        // Ensure type is always percentage
        formData.set('type', 'percentage');
        
        fetch('promo_codes.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                if (isEditMode) {
                    // Update existing row
                    updatePromoCodeRow(currentPromoId, formData);
                } else {
                    // Add new row to table
                    addPromoCodeRow(formData);
                }
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        });
    });
    
    function updatePromoCodeRow(id, formData) {
        const row = document.querySelector(`tr[data-promo-id="${id}"]`);
        if (row) {
            const code = formData.get('code');
            const name = formData.get('name');
            const description = formData.get('description');
            const type = formData.get('type');
            const value = formData.get('value');
            const isActive = formData.get('is_active') === 'on';
            
            // Update the row content
            row.querySelector('.code-cell').innerHTML = `<span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">${code}</span>`;
            row.querySelector('.name-cell').innerHTML = `
                <div>
                    <div class="text-sm font-medium text-gray-900">${name}</div>
                    <div class="text-sm text-gray-500">${description}</div>
                </div>
            `;
            row.querySelector('.type-cell').innerHTML = `
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getTypeClass(type)}">
                    ${type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                </span>
            `;
            row.querySelector('.value-cell').textContent = getValueDisplay(type, value);
            row.querySelector('.status-cell').innerHTML = `
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full status-badge ${isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${isActive ? 'Active' : 'Inactive'}
                </span>
            `;
        }
    }
    
    function addPromoCodeRow(formData) {
        const tbody = document.querySelector('tbody');
        const code = formData.get('code');
        const name = formData.get('name');
        const description = formData.get('description');
        const type = formData.get('type');
        const value = formData.get('value');
        const isActive = formData.get('is_active') === 'on';
        
        const newRow = document.createElement('tr');
        newRow.className = 'bg-white divide-y divide-gray-200';
        newRow.setAttribute('data-promo-id', 'new');
        
        newRow.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap code-cell">
                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">${code}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap name-cell">
                <div>
                    <div class="text-sm font-medium text-gray-900">${name}</div>
                    <div class="text-sm text-gray-500">${description}</div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap type-cell">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getTypeClass(type)}">
                    ${type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 value-cell">
                ${getValueDisplay(type, value)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                0/∞
            </td>
            <td class="px-6 py-4 whitespace-nowrap status-cell">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full status-badge ${isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${isActive ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="editPromoCode('new')" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button onclick="togglePromoCode('new')" class="text-yellow-600 hover:text-yellow-900 mr-3 toggle-btn">
                    ${isActive ? 'Deactivate' : 'Activate'}
                </button>
                <button onclick="deletePromoCode('new')" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
        `;
        
        tbody.appendChild(newRow);
    }
    
    function getTypeClass(type) {
        return 'bg-blue-100 text-blue-800';
    }
    
    function getValueDisplay(type, value) {
        return `${value}%`;
    }
    
    function showNotification(message, type) {
        // Remove existing notifications
        document.querySelectorAll('.notification').forEach(n => n.remove());
        
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        
        const notification = document.createElement('div');
        notification.className = 'notification fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300';
        notification.innerHTML = `
            <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    ${type === 'success' ? '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>' :
                      '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>'}
                </svg>
                <span>${message}</span>
                <button class="notification-close ml-2 hover:opacity-75">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Slide in animation
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
        
        // Manual close
        notification.querySelector('.notification-close').addEventListener('click', function() {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        });
    }
    
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
    
    // Add active class to current menu item
    const currentPage = '<?php echo $currentPage; ?>';
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        if (item.getAttribute('href') && item.getAttribute('href').includes(currentPage)) {
            item.classList.add('active');
        }
    });
    </script>
</body>
</html> 