<?php
// Get video sections from database
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM video_section ORDER BY id ASC");
$videoSections = $stmt->fetchAll();
?>

<!-- Add New Video Section -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Add New Video Section</h2>
        <p class="text-sm text-gray-600 mt-1">Upload a new video for your homepage</p>
    </div>
    
    <form id="addVideoForm" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Video File Upload -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">Video File</label>
                <input type="file" name="video_file" accept="video/*" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                <p class="text-xs text-gray-500 mt-1">Upload MP4, WebM, or other video formats</p>
            </div>
            
            <!-- Slug Text -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">Section Title</label>
                <input type="text" name="slug_text" placeholder="e.g., Beam X WeULT" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                <p class="text-xs text-gray-500 mt-1">Title displayed on the video section</p>
            </div>
            
            <!-- Description -->
            <div class="form-group md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3" placeholder="Enter video description..." required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"></textarea>
                <p class="text-xs text-gray-500 mt-1">Description text below the video</p>
            </div>
            
            <!-- Button Text -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                <input type="text" name="button_text" placeholder="e.g., JOIN COMMUNITY" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                <p class="text-xs text-gray-500 mt-1">Text displayed on the button</p>
            </div>
            
            <!-- Button Link -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                <input type="text" name="button_link" placeholder="e.g., # or shop.php" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                <p class="text-xs text-gray-500 mt-1">Link for the button (use # for no link)</p>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-2 bg-black text-white rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Upload Video Section
            </button>
        </div>
    </form>
</div>

<!-- Video Sections Management -->
<div class="bg-white rounded-lg shadow overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Video Sections</h2>
        <p class="text-sm text-gray-600 mt-1">Manage video sections displayed on your homepage</p>
    </div>
    
    <div class="p-6">
        <?php if (empty($videoSections)): ?>
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-500 text-sm">No video sections found</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($videoSections as $section): ?>
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <?= htmlspecialchars($section['slug_text'] ?? 'Untitled Section') ?>
                                    <span class="ml-2 text-sm font-normal <?= $section['is_active'] ? 'text-green-600' : 'text-red-600' ?>">
                                        (<?= $section['is_active'] ? 'Active' : 'Inactive' ?>)
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($section['description'] ?? 'No description') ?></p>
                                <p class="text-xs text-gray-500">Video: <?= htmlspecialchars($section['video_path'] ?? 'No video') ?></p>
                            </div>
                            <div class="flex space-x-2">
                                <button type="button" onclick="toggleVideoSection(<?= $section['id'] ?>, <?= $section['is_active'] ? 0 : 1 ?>)" 
                                        class="px-3 py-1 <?= $section['is_active'] ? 'bg-yellow-600' : 'bg-green-600' ?> text-white text-xs rounded hover:opacity-80">
                                    <?= $section['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                                <button type="button" onclick="deleteVideoSection(<?= $section['id'] ?>)" 
                                        class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Button Text:</span>
                                <span class="text-gray-600"><?= htmlspecialchars($section['button_text'] ?? 'No button') ?></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Button Link:</span>
                                <span class="text-gray-600"><?= htmlspecialchars($section['button_link'] ?? 'No link') ?></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Created:</span>
                                <span class="text-gray-600"><?= date('M j, Y', strtotime($section['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Add new video form submission
document.getElementById('addVideoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add_video_section');
    
    fetch('../ajax_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Video section added successfully!', 'success');
            // Reset form
            this.reset();
            // Reload page to show new video
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error adding video section: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding video section', 'error');
        console.error('Error:', error);
    });
});

// Toggle video section active status
function toggleVideoSection(sectionId, newStatus) {
    const action = newStatus === 1 ? 'activate' : 'deactivate';
    let confirmMessage = `Are you sure you want to ${action} this video section?`;
    
    // If activating, warn that other sections will be deactivated
    if (newStatus === 1) {
        confirmMessage = `Are you sure you want to activate this video section? This will deactivate all other video sections.`;
    }
    
    if (confirm(confirmMessage)) {
        fetch('../ajax_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=toggle_video_section&section_id=${sectionId}&is_active=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Reload the page to update the status
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(`Error ${action}ing video section: ` + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification(`Error ${action}ing video section`, 'error');
            console.error('Error:', error);
        });
    }
}

// Delete video section
function deleteVideoSection(sectionId) {
    if (confirm('Are you sure you want to delete this video section? This action cannot be undone.')) {
        fetch('../ajax_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_video_section&section_id=${sectionId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Video section deleted successfully!', 'success');
                // Reload the page to update the video list
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error deleting video section: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error deleting video section', 'error');
            console.error('Error:', error);
        });
    }
}

// Notification function
function showNotification(message, type) {
    const notification = document.createElement('div');
    let bgColor = 'bg-red-600';
    if (type === 'success') bgColor = 'bg-green-600';
    if (type === 'info') bgColor = 'bg-blue-600';
    
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${bgColor}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script> 