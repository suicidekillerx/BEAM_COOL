<?php
// Get all footer links with section names
$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT fl.*, fs.section_name 
    FROM footer_links fl 
    LEFT JOIN footer_sections fs ON fl.section_id = fs.id 
    ORDER BY fs.sort_order, fl.sort_order, fl.id
");
$footer_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Footer Links</h2>
            <button type="button" onclick="openAddLinkModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Add Link
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link Text</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($footer_links)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No footer links found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($footer_links as $link): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $link['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($link['section_name'] ?? 'N/A') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($link['link_text']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs"><?= htmlspecialchars($link['link_url']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $link['sort_order'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $link['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $link['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="editLink(<?= $link['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button onclick="deleteItem('footer_links', <?= $link['id'] ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                                <button onclick="toggleActive('footer_links', <?= $link['id'] ?>, <?= $link['is_active'] ? 0 : 1 ?>)" class="ml-3 text-<?= $link['is_active'] ? 'yellow' : 'green' ?>-600 hover:text-<?= $link['is_active'] ? 'yellow' : 'green' ?>-900">
                                    <?= $link['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Link Modal -->
<div id="linkModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Add New Link</h3>
            </div>
            <form id="linkForm" class="bg-white px-4 pb-4 sm:p-6 sm:pb-4">
                <input type="hidden" name="id" id="linkId">
                <div class="mb-4">
                    <label for="section_id" class="block text-sm font-medium text-gray-700">Section</label>
                    <select name="section_id" id="section_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm">
                        <option value="">Select a section</option>
                        <?php foreach ($footer_sections as $section): ?>
                            <option value="<?= $section['id'] ?>"><?= htmlspecialchars($section['section_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="link_text" class="block text-sm font-medium text-gray-700">Link Text</label>
                    <input type="text" name="link_text" id="link_text" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="footer_link_url" class="block text-sm font-medium text-gray-700">URL</label>
                    <input type="url" name="link_url" id="footer_link_url" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="link_sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                    <input type="number" name="sort_order" id="link_sort_order" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="link_is_active" class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                    <label for="link_is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                </div>
            </form>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="saveLinkBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-black text-base font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black sm:ml-3 sm:w-auto sm:text-sm">
                    Save
                </button>
                <button type="button" onclick="closeModal('linkModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Open modal for adding new link
function openAddLinkModal() {
    currentAction = 'add';
    currentId = null;
    
    // Reset form
    const form = document.getElementById('linkForm');
    form.reset();
    
    // Set modal title
    document.getElementById('modalTitle').textContent = 'Add New Link';
    
    // Show modal
    document.getElementById('linkModal').classList.remove('hidden');
}

// Edit link
function editLink(id) {
    currentAction = 'edit';
    currentId = id;
    
    // Find the link data from the PHP-loaded data
    const links = <?= json_encode($footer_links) ?>;
    const link = links.find(l => l.id == id);
    
    if (link) {
                document.getElementById('linkId').value = link.id;
                document.getElementById('section_id').value = link.section_id;
                document.getElementById('link_text').value = link.link_text;
                document.getElementById('link_url').value = link.link_url;
                document.getElementById('sort_order').value = link.sort_order || '';
                document.getElementById('is_active').checked = link.is_active == 1;
                
                // Set modal title
                document.getElementById('modalTitle').textContent = 'Edit Link';
                
                // Show modal
                document.getElementById('linkModal').classList.remove('hidden');
            } else {
        showAlert('Error loading link: Link not found', 'error');
            }
}

// Save link
document.getElementById('saveLinkBtn').addEventListener('click', function() {
    const form = document.getElementById('linkForm');
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
            table: 'footer_links',
            id: currentId,
            data: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Link ${currentAction === 'add' ? 'added' : 'updated'} successfully!`, 'success');
            closeModal('linkModal');
            // Reload the page to see changes
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('Error saving link: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error saving link. Please try again.', 'error');
    });
});

// Close modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Show alert message (reused from footer_sections.php)
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
