<?php
// Get all footer sections
$sections = $footer_sections;
?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Footer Sections</h2>
            <button type="button" onclick="openAddModal('footer_section')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Add Section
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($sections)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No footer sections found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sections as $section): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $section['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($section['section_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $section['sort_order'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $section['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $section['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="editItem('footer_section', <?= $section['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button onclick="deleteItem('footer_sections', <?= $section['id'] ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                                <button onclick="toggleActive('footer_sections', <?= $section['id'] ?>, <?= $section['is_active'] ? 0 : 1 ?>)" class="ml-3 text-<?= $section['is_active'] ? 'yellow' : 'green' ?>-600 hover:text-<?= $section['is_active'] ? 'yellow' : 'green' ?>-900">
                                    <?= $section['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="sectionModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="sectionForm" class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <input type="hidden" name="id" id="sectionId">
                <div class="mb-4">
                    <label for="section_name" class="block text-sm font-medium text-gray-700">Section Name</label>
                    <input type="text" name="section_name" id="section_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="section_sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                    <input type="number" name="sort_order" id="section_sort_order" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="section_is_active" class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                    <label for="section_is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                </div>
            </form>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="saveSectionBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-black text-base font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black sm:ml-3 sm:w-auto sm:text-sm">
                    Save
                </button>
                <button type="button" onclick="closeModal('sectionModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentAction = 'add';
let currentId = null;

// Open modal for adding/editing
function openAddModal(type) {
    currentAction = 'add';
    currentId = null;
    
    // Reset form
    const form = document.getElementById('sectionForm');
    form.reset();
    
    // Set modal title
    document.getElementById('modalTitle').textContent = 'Add New Section';
    
    // Show modal
    document.getElementById('sectionModal').classList.remove('hidden');
}

// Edit item
function editItem(type, id) {
    currentAction = 'edit';
    currentId = id;
    
    // Fetch item data
    fetch('get_item.php?table=footer_sections&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.data;
                document.getElementById('sectionId').value = item.id;
                document.getElementById('section_name').value = item.section_name;
                document.getElementById('section_sort_order').value = item.sort_order || '';
                document.getElementById('section_is_active').checked = item.is_active == 1;
                
                // Set modal title
                document.getElementById('modalTitle').textContent = 'Edit Section';
                
                // Show modal
                document.getElementById('sectionModal').classList.remove('hidden');
            } else {
                showAlert('Error loading section: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading section. Please try again.', 'error');
        });
}

// Save section
document.getElementById('saveSectionBtn').addEventListener('click', function() {
    const form = document.getElementById('sectionForm');
    const formData = new FormData(form);
    const data = {};
    
    // Convert form data to object
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    // Add is_active value (checkbox handling)
    data.is_active = form.elements.is_active.checked ? 1 : 0;
    
    // Determine action URL
    const action = currentAction === 'add' ? 'add' : 'edit';
    
    // Send data to server
    fetch('setting.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: action,
            table: 'footer_sections',
            id: currentId,
            data: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Section ${currentAction === 'add' ? 'added' : 'updated'} successfully!`, 'success');
            closeModal('sectionModal');
            // Reload the page to see changes
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('Error saving section: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error saving section. Please try again.', 'error');
    });
});

// Close modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Delete item
function deleteItem(table, id) {
    if (confirm('Are you sure you want to delete this item?')) {
        fetch('setting.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                table: table,
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Item deleted successfully!', 'success');
                // Reload the page to see changes
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Error deleting item: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error deleting item. Please try again.', 'error');
        });
    }
}

// Toggle active status
function toggleActive(table, id, newStatus) {
    fetch('setting.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'toggle_active',
            table: table,
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Item ${newStatus ? 'activated' : 'deactivated'} successfully!`, 'success');
            // Reload the page to see changes
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('Error updating item: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating item. Please try again.', 'error');
    });
}

// Show alert message
function showAlert(message, type = 'success') {
    // Create alert element if it doesn't exist
    let alert = document.getElementById('alertMessage');
    if (!alert) {
        alert = document.createElement('div');
        alert.id = 'alertMessage';
        alert.className = 'fixed top-4 right-4 p-4 rounded-md shadow-lg z-50';
        document.body.appendChild(alert);
    }
    
    // Set alert content and style
    alert.textContent = message;
    alert.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
    
    // Remove alert after 3 seconds
    setTimeout(() => {
        alert.remove();
    }, 3000);
}
</script>
