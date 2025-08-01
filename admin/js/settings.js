// Global variables for modal handling
let currentAction = 'add';
let currentId = null;

// Generic function to handle API calls
async function makeRequest(action, table, data = null, id = null) {
    const requestData = {
        action,
        table,
        data,
        id
    };
    
    console.log('Making request:', requestData);
    
    try {
        const response = await fetch('setting.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        console.log('Response status:', response.status);
        const result = await response.json();
        console.log('Response data:', result);
        
        if (!result.success) {
            throw new Error(result.error || 'Operation failed');
        }
        return result;
    } catch (error) {
        console.error('Request failed:', error);
        console.error('Request data was:', requestData);
        throw error;
    }
}

// Footer Sections
async function addFooterSection(data) {
    await makeRequest('add', 'footer_sections', data);
    location.reload();
}

async function editFooterSection(id, data) {
    await makeRequest('edit', 'footer_sections', data, id);
    location.reload();
}

async function deleteFooterSection(id) {
    if (confirm('Are you sure you want to delete this section?')) {
        await makeRequest('delete', 'footer_sections', null, id);
        location.reload();
    }
}

async function toggleFooterSection(id) {
    await makeRequest('toggle_active', 'footer_sections', null, id);
    location.reload();
}

// Footer Links
async function addFooterLink(data) {
    await makeRequest('add', 'footer_links', data);
    location.reload();
}

async function editFooterLink(id, data) {
    await makeRequest('edit', 'footer_links', data, id);
    location.reload();
}

async function deleteFooterLink(id) {
    if (confirm('Are you sure you want to delete this link?')) {
        await makeRequest('delete', 'footer_links', null, id);
        location.reload();
    }
}

async function toggleFooterLink(id) {
    await makeRequest('toggle_active', 'footer_links', null, id);
    location.reload();
}

// Social Media
async function addSocialMedia(data) {
    await makeRequest('add', 'social_media', data);
    location.reload();
}

async function editSocialMedia(id, data) {
    await makeRequest('edit', 'social_media', data, id);
    location.reload();
}

async function deleteSocialMedia(id) {
    if (confirm('Are you sure you want to delete this social media link?')) {
        await makeRequest('delete', 'social_media', null, id);
        location.reload();
    }
}

async function toggleSocialMedia(id) {
    await makeRequest('toggle_active', 'social_media', null, id);
    location.reload();
}

// Video Section functions
async function addVideoSection(data) {
    console.log('Adding video section with data:', data);
    try {
        const result = await makeRequest('add', 'video_section', data);
        console.log('Add video section result:', result);
        showAlert('Video section added successfully!', 'success');
        // Note: UI updates are handled in the video section partial
    } catch (error) {
        console.error('Error adding video section:', error);
        showAlert('Error adding video section: ' + error.message, 'error');
    }
}

async function editVideoSection(id, data) {
    console.log('Editing video section ID:', id, 'with data:', data);
    try {
        const result = await makeRequest('edit', 'video_section', data, id);
        console.log('Edit video section result:', result);
        showAlert('Video section updated successfully!', 'success');
        // Note: UI updates are handled in the video section partial
    } catch (error) {
        console.error('Error editing video section:', error);
        showAlert('Error updating video section: ' + error.message, 'error');
    }
}

async function deleteVideoSection(id) {
    if (confirm('Are you sure you want to delete this video section?')) {
        try {
            console.log('Deleting video section ID:', id);
            const result = await makeRequest('delete', 'video_section', null, id);
            console.log('Delete video section result:', result);
            showAlert('Video section deleted successfully!', 'success');
            // Note: UI updates are handled in the video section partial
        } catch (error) {
            console.error('Error deleting video section:', error);
            showAlert('Error deleting video section: ' + error.message, 'error');
        }
    }
}

async function toggleVideoSection(id) {
    console.log('Toggling video section ID:', id);
    try {
        const result = await makeRequest('toggle_active', 'video_section', null, id);
        console.log('Toggle video section result:', result);
        showAlert('Video section status updated!', 'success');
        // Note: UI updates are handled in the video section partial
    } catch (error) {
        console.error('Error toggling video section:', error);
        showAlert('Error updating video section status: ' + error.message, 'error');
    }
}

// Modal handling
function openAddModal(type) {
    const modal = document.getElementById(`${type}Modal`);
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeModal(type) {
    const modal = document.getElementById(`${type}Modal`);
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Form handling
function handleSubmit(event, formId, actionFunction) {
    event.preventDefault();
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    actionFunction(data);
}

// Generic delete function
async function deleteItem(table, id) {
    if (confirm('Are you sure you want to delete this item?')) {
        try {
            await makeRequest('delete', table, null, id);
            location.reload();
        } catch (error) {
            console.error('Error deleting item:', error);
        }
    }
}

// Generic toggle active function
async function toggleActive(table, id, newStatus) {
    try {
        await makeRequest('toggle_active', table, null, id);
        location.reload();
    } catch (error) {
        console.error('Error toggling item:', error);
    }
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
