<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set current page for sidebar highlighting
$currentPage = 'about-us';
$pageTitle = 'About Page Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_content':
                $section = $_POST['section'];
                $key = $_POST['key'];
                $value = $_POST['value'];
                
                if (updateAboutContent($section, $key, $value)) {
                    $success_message = "Content updated successfully!";
                } else {
                    $error_message = "Failed to update content.";
                }
                break;
                
            case 'add_content':
                $section = $_POST['section'];
                $key = $_POST['key'];
                $type = $_POST['type'];
                $value = $_POST['value'];
                $sort_order = intval($_POST['sort_order']);
                
                if (insertAboutContent($section, $key, $type, $value, $sort_order)) {
                    $success_message = "Content added successfully!";
                } else {
                    $error_message = "Failed to add content.";
                }
                break;
                
            case 'delete_content':
                $section = $_POST['section'];
                $key = $_POST['key'];
                
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("UPDATE aboutus SET is_active = 0 WHERE section_name = ? AND content_key = ?");
                if ($stmt->execute([$section, $key])) {
                    $success_message = "Content deleted successfully!";
                } else {
                    $error_message = "Failed to delete content.";
                }
                break;
        }
    }
}

// Get all about content
$aboutContent = getAllAboutContent();

// Get all sections for organization
$sections = [
    'hero' => 'Hero Section',
    'story' => 'Story Section', 
    'mission_vision' => 'Mission & Vision',
    'stats' => 'Statistics',
    'values' => 'Our Values',
    'timeline' => 'Timeline',
    'team' => 'Team Members',
    'cta' => 'Call to Action'
];

$contentTypes = [
    'text' => 'Text',
    'html' => 'HTML',
    'image' => 'Image URL',
    'url' => 'URL',
    'number' => 'Number'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - <?php echo $pageTitle; ?></title>
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
        
        .content-area {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
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
        
        .section-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            margin-bottom: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .section-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .section-header {
            background: #f9fafb;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
        }
        .content-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f3f4f6;
        }
        .content-item:last-child {
            border-bottom: none;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-success {
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-success:hover {
            background: #059669;
        }
        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            min-height: 80px;
            resize: vertical;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .preview-link {
            display: inline-block;
            background: #6366f1;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .preview-link:hover {
            background: #4f46e5;
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
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">About Page Management</h1>
                    <div class="flex gap-3">
                        <a href="../about.php" target="_blank" class="preview-link">
                            Preview About Page
                        </a>
                        <button onclick="openAddModal()" class="btn-success">
                            Add New Content
                        </button>
                    </div>
                </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Content Management -->
            <?php foreach ($sections as $sectionKey => $sectionName): ?>
                <div class="section-card">
                    <div class="section-header">
                        <h2><?php echo $sectionName; ?></h2>
                    </div>
                    
                    <?php if (isset($aboutContent[$sectionKey])): ?>
                        <?php foreach ($aboutContent[$sectionKey] as $key => $value): ?>
                            <div class="content-item">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <strong class="text-sm text-gray-700"><?php echo htmlspecialchars($key); ?></strong>
                                            <span class="text-xs bg-gray-200 px-2 py-1 rounded">
                                                <?php 
                                                // Get content type from database
                                                $pdo = getDBConnection();
                                                $stmt = $pdo->prepare("SELECT content_type FROM aboutus WHERE section_name = ? AND content_key = ?");
                                                $stmt->execute([$sectionKey, $key]);
                                                $result = $stmt->fetch();
                                                echo $result ? $contentTypes[$result['content_type']] : 'Text';
                                                ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mb-2">
                                            <?php 
                                            if (strlen($value) > 200) {
                                                echo htmlspecialchars(substr($value, 0, 200)) . '...';
                                            } else {
                                                echo htmlspecialchars($value);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 ml-4">
                                        <button onclick="openEditModal('<?php echo $sectionKey; ?>', '<?php echo $key; ?>', <?php echo htmlspecialchars(json_encode($value)); ?>)" class="btn-primary">
                                            Edit
                                        </button>
                                        <button onclick="deleteContent('<?php echo $sectionKey; ?>', '<?php echo $key; ?>')" class="btn-danger">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="content-item">
                            <p class="text-gray-500 italic">No content found for this section.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </main>
        </div>
    </div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2 class="text-xl font-bold mb-4">Edit Content</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_content">
            <input type="hidden" name="section" id="editSection">
            <input type="hidden" name="key" id="editKey">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Section:</label>
                <input type="text" id="editSectionDisplay" class="form-input" readonly>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Key:</label>
                <input type="text" id="editKeyDisplay" class="form-input" readonly>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Content:</label>
                <textarea name="value" id="editValue" class="form-textarea" required></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Update Content</button>
                <button type="button" onclick="closeEditModal()" class="btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">&times;</span>
        <h2 class="text-xl font-bold mb-4">Add New Content</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_content">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Section:</label>
                <select name="section" class="form-input" required>
                    <option value="">Select Section</option>
                    <?php foreach ($sections as $key => $name): ?>
                        <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Key:</label>
                <input type="text" name="key" class="form-input" required placeholder="e.g., title, subtitle, content">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Content Type:</label>
                <select name="type" class="form-input" required>
                    <?php foreach ($contentTypes as $key => $name): ?>
                        <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Content:</label>
                <textarea name="value" class="form-textarea" required></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order:</label>
                <input type="number" name="sort_order" class="form-input" value="0" min="0">
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="btn-success">Add Content</button>
                <button type="button" onclick="closeAddModal()" class="btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(section, key, value) {
    document.getElementById('editSection').value = section;
    document.getElementById('editKey').value = key;
    document.getElementById('editSectionDisplay').value = section;
    document.getElementById('editKeyDisplay').value = key;
    document.getElementById('editValue').value = value;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}

function deleteContent(section, key) {
    if (confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_content">
            <input type="hidden" name="section" value="${section}">
            <input type="hidden" name="key" value="${key}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    const addModal = document.getElementById('addModal');
    
    if (event.target === editModal) {
        editModal.style.display = 'none';
    }
    if (event.target === addModal) {
        addModal.style.display = 'none';
    }
}

// Auto-refresh preview when content is updated
<?php if (isset($success_message)): ?>
setTimeout(() => {
    const previewWindow = window.open('../about.php', 'preview');
    if (previewWindow) {
        previewWindow.location.reload();
    }
}, 1000);
<?php endif; ?>
</script>

</body>
</html>
