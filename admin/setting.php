<?php
require_once 'includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
requireAuth();

// Handle AJAX requests (add/edit/delete/toggle/save for all tables)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = file_get_contents('php://input');
    error_log("POST request received - Raw input: " . $input);
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none'));
    $isJson = false;
    $dataArr = [];
    $allowedTables = ['site_settings', 'footer_sections', 'footer_links', 'social_media', 'video_section'];
    // Accept both JSON and form-encoded requests for all actions
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $dataArr = json_decode($input, true);
        $isJson = true;
    } else {
        // Merge $_POST and $_FILES (if any)
        $dataArr = $_POST;
        // If data is sent as form-data with a 'data' field (JSON string), decode it
        if (isset($dataArr['data']) && is_string($dataArr['data']) && ($json = json_decode($dataArr['data'], true))) {
            $dataArr['data'] = $json;
        }
    }
    $action = $dataArr['action'] ?? null;
    $pdo = getDBConnection();
    try {
        $table = $dataArr['table'] ?? null;
        if ($table && !in_array($table, $allowedTables)) {
            throw new Exception('Invalid table');
        }
        // Save site settings (special case)
        if ($action === 'save_site_settings' && isset($dataArr['data']) && is_array($dataArr['data'])) {
            error_log("Saving site settings: " . print_r($dataArr['data'], true));
            $settings = $dataArr['data'];
            $savedCount = 0;
            

            
            foreach ($settings as $key => $value) {
                try {
                    // Upsert (insert or update) each setting
                    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                    $result = $stmt->execute([$key, $value]);
                    if ($result) {
                        $savedCount++;
                        error_log("Saved setting: $key = " . ($key === 'maintenance_password' ? '[HIDDEN]' : $value));
                    } else {
                        error_log("Failed to save setting: $key = $value");
                    }
                } catch (Exception $e) {
                    error_log("Error saving setting $key: " . $e->getMessage());
                }
            }
            error_log("Total settings saved: $savedCount");
            echo json_encode(['success' => true, 'saved_count' => $savedCount]);
            exit;
        }
        // Handle generic table actions (add/edit/delete/toggle_active)
        $id = isset($dataArr['id']) ? intval($dataArr['id']) : null;
        // Accept data as JSON string for legacy forms
        if (isset($dataArr['data']) && is_string($dataArr['data']) && ($json = json_decode($dataArr['data'], true))) {
            $dataArr['data'] = $json;
        }
        if ($action === 'delete' && $id && $table) {
            error_log("Delete request - Table: $table, ID: $id");
            try {
                $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
                $result = $stmt->execute([$id]);
                $rowsAffected = $stmt->rowCount();
                error_log("Delete result - Success: " . ($result ? 'true' : 'false') . ", Rows affected: $rowsAffected");
                echo json_encode(['success' => true, 'rows_affected' => $rowsAffected]);
            } catch (Exception $e) {
                error_log("Delete error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
        if ($action === 'toggle_active' && $id && $table) {
            $stmt = $pdo->prepare("UPDATE `$table` SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }
        if (($action === 'add' || $action === 'edit') && $table) {
            error_log("Processing $action for table: $table");
            $data = $dataArr['data'] ?? [];
            error_log("Received data: " . print_r($data, true));
            
            // Special handling for footer_sections: make section_title = section_name
            if ($table === 'footer_sections' && isset($data['section_name'])) {
                $data['section_title'] = $data['section_name'];
                error_log("Set section_title = section_name: " . $data['section_title']);
            }
            
            // Validate fields
            $columns = array_keys($pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_UNIQUE));
            error_log("Table columns: " . implode(', ', $columns));
            
            $fields = array_intersect(array_keys($data), $columns);
            error_log("Valid fields: " . implode(', ', $fields));
            
            $placeholders = array_map(fn($f) => '?', $fields);
            $values = array_map(fn($f) => $data[$f], $fields);
            error_log("Values: " . print_r($values, true));
            
            if ($action === 'add') {
                error_log("Adding new record to $table");
                $sql = "INSERT INTO `$table` (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
                error_log("SQL: $sql");
                
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($values);
                error_log("Insert result: " . ($result ? 'success' : 'failed'));
                
                if ($result) {
                    $id = $pdo->lastInsertId();
                    error_log("New ID: $id");
                    $row = $pdo->query("SELECT * FROM `$table` WHERE id = " . intval($id))->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(['success' => true, 'id' => $id, 'row' => $row]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to insert record']);
                }
                exit;
            } else if ($action === 'edit' && isset($dataArr['id'])) {
                error_log("Editing record ID: " . $dataArr['id']);
                $set = implode(',', array_map(fn($f) => "$f = ?", $fields));
                $sql = "UPDATE `$table` SET $set WHERE id = ?";
                error_log("SQL: $sql");
                
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([...$values, intval($dataArr['id'])]);
                error_log("Update result: " . ($result ? 'success' : 'failed'));
                
                if ($result) {
                    $row = $pdo->query("SELECT * FROM `$table` WHERE id = " . intval($dataArr['id']))->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(['success' => true, 'row' => $row]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update record']);
                }
                exit;
            }
        }

        
        // If we reach here, action is not recognized or missing required params
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action or missing parameters',
            'debug' => [
                'action' => $action,
                'table' => $table,
                'id' => $id,
                'data' => $data,
                'dataArr' => $dataArr
            ]
        ]);
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}



// Fetch all data for display
function fetchAll($table) {
    $pdo = getDBConnection();
    // Only use sort_order if the table has it
    $tablesWithSortOrder = ['footer_sections', 'footer_links', 'social_media'];
    if (in_array($table, $tablesWithSortOrder)) {
        $order = "ORDER BY sort_order ASC, id ASC";
    } else {
        $order = "ORDER BY id ASC";
    }
    return $pdo->query("SELECT * FROM `$table` $order")->fetchAll(PDO::FETCH_ASSOC);
}
$site_settings = fetchAll('site_settings');
$footer_sections = fetchAll('footer_sections');
$footer_links = fetchAll('footer_links');
$social_media = fetchAll('social_media');
// Get video sections with proper ordering (no sort_order column)
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM video_section ORDER BY created_at DESC");
$stmt->execute();
$video_sections = $stmt->fetchAll();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .tab-btn.active { background: #000; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']">
    <div class="flex h-screen">
        <?php $currentPage = 'settings'; include 'includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php $pageTitle = 'Site Settings'; include 'includes/header.php'; ?>
            <main class="content-area flex-1 overflow-y-auto p-6">
                <h1 class="text-3xl font-bold mb-6">Site Settings & Footer</h1>
                <div class="mb-6 flex space-x-2">
                    <button class="tab-btn px-4 py-2 rounded active" data-tab="site_settings">Site Settings</button>
                    <button class="tab-btn px-4 py-2 rounded" data-tab="video_section">Video Section</button>
                    <button class="tab-btn px-4 py-2 rounded" data-tab="footer_sections">Footer Sections</button>
                    <button class="tab-btn px-4 py-2 rounded" data-tab="footer_links">Footer Links</button>
                    <button class="tab-btn px-4 py-2 rounded" data-tab="social_media">Social Media</button>
                    <button class="tab-btn px-4 py-2 rounded" data-tab="login">Login</button>
                </div>
                <div id="tab-site_settings" class="tab-content active">
                    <?php include 'partials/settings_site_settings.php'; ?>
                </div>
                <div id="tab-video_section" class="tab-content">
                    <?php include 'partials/settings_video_section.php'; ?>
                </div>
                <div id="tab-footer_sections" class="tab-content">
                    <?php include 'partials/settings_footer_sections.php'; ?>
                </div>
                <div id="tab-footer_links" class="tab-content">
                    <?php include 'partials/settings_footer_links.php'; ?>
                </div>
                <div id="tab-social_media" class="tab-content">
                    <?php include 'partials/settings_social_media.php'; ?>
                </div>
                <div id="tab-login" class="tab-content">
                    <?php include 'partials/settings_login.php'; ?>
                </div>
            </main>
        </div>
    </div>
    <script>
    // Debug: Check if settings.js loaded properly
    console.log('Settings.js loaded. Available functions:', {
        deleteVideoSection: typeof deleteVideoSection,
        makeRequest: typeof makeRequest,
        deleteItem: typeof deleteItem
    });
    </script>
    <script>
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });
    
    // Mobile sidebar functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 1024) { // Only on mobile
                const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
                const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnToggle && sidebar && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    });
    </script>
</body>
</html>            
    </script>
</body>
</html>
