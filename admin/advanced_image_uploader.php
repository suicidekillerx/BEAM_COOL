<?php
// This file contains the HTML and JavaScript for the advanced image uploader
// It will be included in the products.php file
?>

<!-- Advanced Image Uploader Component -->
<div class="advanced-image-uploader">
    <label class="block text-sm font-medium text-gray-700 mb-2">Product Images</label>
    
    <!-- Upload Area -->
    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors duration-200 relative mb-4">
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <input type="file" id="imageInput" multiple accept="image/*" 
               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
        <p class="mt-2 text-sm text-gray-600">Click to upload images or drag & drop</p>
        <p class="text-xs text-gray-500">First image will be Primary, second will be Secondary. Allowed: JPG, PNG, GIF, WEBP</p>
    </div>
    
    <!-- Image Gallery -->
    <div id="imageGallery" class="space-y-4">
        <!-- Primary and Secondary Images Section with Drag & Drop -->
        <div class="primary-secondary-section">
            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                Primary & Secondary Images (Drag to reorder)
            </h4>
            <div class="grid grid-cols-2 gap-4">
                <div id="primaryImageContainer" class="min-h-[120px] border-2 border-dashed border-yellow-300 rounded-lg p-4 bg-yellow-50 flex items-center justify-center" data-drop-zone="primary">
                    <p class="text-sm text-gray-500">No primary image selected</p>
                </div>
                <div id="secondaryImageContainer" class="min-h-[120px] border-2 border-dashed border-blue-300 rounded-lg p-4 bg-blue-50 flex items-center justify-center" data-drop-zone="secondary">
                    <p class="text-sm text-gray-500">No secondary image selected</p>
                </div>
            </div>
            <!-- Swap Buttons -->
            <div class="swap-buttons">
                <button id="swapPrimarySecondary" class="swap-btn" onclick="imageUploader.swapPrimarySecondary()" disabled>
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                    Swap Primary ↔ Secondary
                </button>
                <div class="swap-hint text-xs text-gray-500 mt-2 text-center">
                    💡 Tip: You can also drag images to swap them
                </div>
            </div>
        </div>
        
        <!-- Additional Images Section -->
        <div class="additional-section">
            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                <span class="w-3 h-3 bg-gray-500 rounded-full mr-2"></span>
                Additional Images
            </h4>
            <div id="additionalImagesContainer" class="grid grid-cols-4 gap-2 min-h-[80px]">
                <p class="text-sm text-gray-500 col-span-4 flex items-center justify-center">No additional images</p>
            </div>
        </div>
    </div>
    
    <!-- Hidden form inputs for submission -->
    <div id="hiddenInputs"></div>
    
    <!-- Image Counter -->
    <div class="mt-2 text-xs text-gray-500">
        <span id="imageCounter">0 images selected</span>
    </div>
</div>

<style>
.advanced-image-uploader {
    position: relative;
}

.image-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.image-item:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.image-item:hover .image-overlay {
    opacity: 1;
}

.image-actions {
    display: flex;
    gap: 4px;
}

.image-action-btn {
    background: white;
    border: none;
    border-radius: 4px;
    padding: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.image-action-btn:hover {
    background: #f3f4f6;
    transform: scale(1.1);
}

.image-action-btn svg {
    width: 16px;
    height: 16px;
}

.primary-image {
    border: 2px solid #f59e0b;
    background: #fef3c7;
}

.secondary-image {
    border: 2px solid #3b82f6;
    background: #dbeafe;
}

.additional-image {
    border: 2px solid #6b7280;
    background: #f9fafb;
}

.drag-handle {
    cursor: grab;
    position: absolute;
    top: 4px;
    left: 4px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    border-radius: 6px;
    padding: 4px 6px;
    font-size: 12px;
    font-weight: bold;
    transition: all 0.2s ease;
    z-index: 10;
}

.drag-handle:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
    cursor: grabbing;
}

.drag-handle:active {
    cursor: grabbing;
    transform: scale(0.95);
}

.order-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
}

.swap-buttons {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    justify-content: center;
}

.swap-btn {
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.swap-btn:hover {
    background: #2563eb;
}

.swap-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    opacity: 0.5;
}

.swap-btn:not(:disabled):hover {
    background: #2563eb;
    transform: scale(1.05);
}

.swap-btn:not(:disabled):active {
    transform: scale(0.95);
}

.drag-over {
    border-color: #3b82f6;
    background: #dbeafe;
    transform: scale(1.02);
}

.dragging {
    opacity: 0.7;
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.drop-zone-active {
    border-color: #10b981;
    background: #d1fae5;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

.swap-hint {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.already-positioned {
    opacity: 0.8;
}

.position-indicator {
    position: absolute;
    bottom: 4px;
    left: 4px;
    background: #10b981;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    z-index: 10;
}

.duplicate-notification {
    max-width: 300px;
}
</style>

<script>
class AdvancedImageUploader {
    constructor() {
        this.images = [];
        this.primaryIndex = -1;
        this.secondaryIndex = -1;
        this.additionalIndices = [];
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.updateDisplay();
    }
    
    setupEventListeners() {
        const input = document.getElementById('imageInput');
        const uploadArea = input.parentElement;
        
        // File input change
        input.addEventListener('change', (e) => {
            this.handleFileSelection(e.target.files);
        });
        
        // Drag and drop for file upload
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            this.handleFileSelection(e.dataTransfer.files);
        });
        
        // Drag and drop for reordering
        this.setupDragAndDrop();
    }
    
    setupDragAndDrop() {
        const primaryContainer = document.getElementById('primaryImageContainer');
        const secondaryContainer = document.getElementById('secondaryImageContainer');
        
        // Make containers droppable
        [primaryContainer, secondaryContainer].forEach(container => {
            container.addEventListener('dragover', (e) => {
                e.preventDefault();
                container.classList.add('drag-over', 'drop-zone-active');
            });
            
            container.addEventListener('dragleave', (e) => {
                e.preventDefault();
                container.classList.remove('drag-over', 'drop-zone-active');
            });
            
            container.addEventListener('drop', (e) => {
                e.preventDefault();
                container.classList.remove('drag-over', 'drop-zone-active');
                this.handleImageDrop(e, container);
            });
        });
    }
    
    handleImageDrop(e, targetContainer) {
        const imageId = e.dataTransfer.getData('text/plain');
        const dropZone = targetContainer.dataset.dropZone;
        
        if (dropZone === 'primary') {
            this.moveToPrimary(parseFloat(imageId));
            this.showSwapNotification('Image moved to Primary position');
        } else if (dropZone === 'secondary') {
            this.moveToSecondary(parseFloat(imageId));
            this.showSwapNotification('Image moved to Secondary position');
        }
    }
    
    showSwapNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'swap-notification fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
        notification.textContent = message;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    async handleFileSelection(files) {
        const newImages = Array.from(files).filter(file => file.type.startsWith('image/'));
        
        if (newImages.length === 0) return;
        
        // Show loading state
        this.showProcessingState('Checking for duplicates...');
        
        // Check for duplicates and add only unique images
        const uniqueImages = [];
        const duplicates = [];
        
        for (const file of newImages) {
            const isDuplicate = await this.checkForDuplicate(file);
            if (isDuplicate) {
                duplicates.push(file.name);
            } else {
                uniqueImages.push(file);
            }
        }
        
        // Hide loading state
        this.hideProcessingState();
        
        // Show warning for duplicates
        if (duplicates.length > 0) {
            this.showDuplicateWarning(duplicates);
        }
        
        // Clear the file input to prevent accidental re-uploads
        const fileInput = document.getElementById('imageInput');
        if (fileInput) {
            fileInput.value = '';
        }
        
        // Add unique images to the array
        uniqueImages.forEach(file => {
            const imageData = {
                file: file,
                id: Date.now() + Math.random(),
                url: URL.createObjectURL(file),
                hash: null // Will be set after hash calculation
            };
            this.images.push(imageData);
            
            // Calculate hash for the image
            this.calculateFileHash(file).then(hash => {
                imageData.hash = hash;
            });
        });
        
        // Auto-assign primary and secondary if not set
        if (this.primaryIndex === -1 && this.images.length > 0) {
            this.primaryIndex = 0;
        }
        if (this.secondaryIndex === -1 && this.images.length > 1) {
            this.secondaryIndex = 1;
        }
        
        this.updateAdditionalIndices();
        this.updateDisplay();
    }
    
    async checkForDuplicate(newFile) {
        // Check by file name first (quick check)
        const existingNames = this.images.map(img => img.file.name);
        if (existingNames.includes(newFile.name)) {
            return true;
        }
        
        // Check by file size
        const existingSizes = this.images.map(img => img.file.size);
        if (existingSizes.includes(newFile.size)) {
            // If size matches, do a more thorough check
            const newFileHash = await this.calculateFileHash(newFile);
            const existingHashes = this.images.map(img => img.hash).filter(hash => hash !== null);
            
            if (existingHashes.includes(newFileHash)) {
                return true;
            }
        }
        
        return false;
    }
    
    async calculateFileHash(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const arrayBuffer = e.target.result;
                const uint8Array = new Uint8Array(arrayBuffer);
                
                // Simple hash function (you can use a more sophisticated one if needed)
                let hash = 0;
                for (let i = 0; i < uint8Array.length; i++) {
                    const char = uint8Array[i];
                    hash = ((hash << 5) - hash) + char;
                    hash = hash & hash; // Convert to 32-bit integer
                }
                resolve(hash.toString());
            };
            reader.readAsArrayBuffer(file);
        });
    }
    
    showDuplicateWarning(duplicateNames) {
        const notification = document.createElement('div');
        notification.className = 'duplicate-notification fixed top-4 right-4 bg-yellow-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <span>Duplicate images skipped: ${duplicateNames.join(', ')}</span>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
    
    showProcessingState(message) {
        const uploadArea = document.querySelector('.border-2.border-dashed.border-gray-300');
        if (uploadArea) {
            const processingDiv = document.createElement('div');
            processingDiv.id = 'processingState';
            processingDiv.className = 'absolute inset-0 bg-blue-500 bg-opacity-90 text-white flex items-center justify-center rounded-lg z-10';
            processingDiv.innerHTML = `
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <p class="text-sm font-medium">${message}</p>
                </div>
            `;
            uploadArea.appendChild(processingDiv);
        }
    }
    
    hideProcessingState() {
        const processingDiv = document.getElementById('processingState');
        if (processingDiv) {
            processingDiv.remove();
        }
    }
    
    updateAdditionalIndices() {
        this.additionalIndices = this.images
            .map((_, index) => index)
            .filter(index => index !== this.primaryIndex && index !== this.secondaryIndex);
    }
    
    updateDisplay() {
        this.updatePrimaryDisplay();
        this.updateSecondaryDisplay();
        this.updateAdditionalDisplay();
        this.updateCounter();
        this.updateHiddenInputs();
        this.updateSwapButton();
        this.setupDragListeners();
        
        // Add subtle animation to show the change
        this.animateSwap();
    }
    
    animateSwap() {
        const primaryContainer = document.getElementById('primaryImageContainer');
        const secondaryContainer = document.getElementById('secondaryImageContainer');
        
        if (primaryContainer && secondaryContainer) {
            // Add a brief highlight animation
            [primaryContainer, secondaryContainer].forEach(container => {
                container.style.transition = 'all 0.3s ease';
                container.style.transform = 'scale(1.02)';
                container.style.boxShadow = '0 4px 12px rgba(59, 130, 246, 0.3)';
                
                setTimeout(() => {
                    container.style.transform = 'scale(1)';
                    container.style.boxShadow = '';
                }, 300);
            });
        }
    }
    
    updatePrimaryDisplay() {
        const container = document.getElementById('primaryImageContainer');
        container.innerHTML = '';
        
        if (this.primaryIndex >= 0 && this.images[this.primaryIndex]) {
            const image = this.images[this.primaryIndex];
            container.innerHTML = this.createImageHTML(image, 'primary', 1);
        } else {
            container.innerHTML = '<p class="text-sm text-gray-500">No primary image selected</p>';
        }
    }
    
    updateSecondaryDisplay() {
        const container = document.getElementById('secondaryImageContainer');
        container.innerHTML = '';
        
        if (this.secondaryIndex >= 0 && this.images[this.secondaryIndex]) {
            const image = this.images[this.secondaryIndex];
            container.innerHTML = this.createImageHTML(image, 'secondary', 2);
        } else {
            container.innerHTML = '<p class="text-sm text-gray-500">No secondary image selected</p>';
        }
    }
    
    updateAdditionalDisplay() {
        const container = document.getElementById('additionalImagesContainer');
        container.innerHTML = '';
        
        if (this.additionalIndices.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500 col-span-4 flex items-center justify-center">No additional images</p>';
            return;
        }
        
        this.additionalIndices.forEach((index, position) => {
            const image = this.images[index];
            const imageHTML = this.createImageHTML(image, 'additional', position + 3);
            container.innerHTML += imageHTML;
        });
    }
    
    createImageHTML(image, type, order) {
        const isDraggable = type === 'primary' || type === 'secondary';
        const isAlreadyInPosition = (type === 'primary' && this.primaryIndex === this.images.findIndex(img => img.id === image.id)) ||
                                   (type === 'secondary' && this.secondaryIndex === this.images.findIndex(img => img.id === image.id));
        
        return `
            <div class="image-item ${type}-image ${isAlreadyInPosition ? 'already-positioned' : ''}" data-image-id="${image.id}" ${isDraggable ? 'draggable="true"' : ''}>
                <img src="${image.url}" alt="Product image ${order}" />
                <div class="image-overlay">
                    <div class="image-actions">
                        ${type === 'primary' ? `
                            <button class="image-action-btn" onclick="imageUploader.moveToSecondary(${image.id})" title="Move to Secondary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </button>
                        ` : type === 'secondary' ? `
                            <button class="image-action-btn" onclick="imageUploader.moveToPrimary(${image.id})" title="Move to Primary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </button>
                        ` : `
                            <button class="image-action-btn" onclick="imageUploader.moveToPrimary(${image.id})" title="Move to Primary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </button>
                            <button class="image-action-btn" onclick="imageUploader.moveToSecondary(${image.id})" title="Move to Secondary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </button>
                        `}
                        <button class="image-action-btn" onclick="imageUploader.removeImage(${image.id})" title="Remove">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                ${isDraggable ? '<div class="drag-handle" title="Drag to reorder">⋮⋮</div>' : ''}
                <div class="order-badge">${order}</div>
                ${isAlreadyInPosition ? '<div class="position-indicator" title="Already in position">✓</div>' : ''}
            </div>
        `;
    }
    
    moveToPrimary(imageId) {
        const index = this.images.findIndex(img => img.id === imageId);
        if (index === -1) return;
        
        // Check if this image is already primary
        if (this.primaryIndex === index) {
            this.showSwapNotification('Image is already primary');
            return;
        }
        
        // If this image is currently secondary, clear the secondary position
        
        
        // If there's already a primary image, move it to secondary
        if (this.primaryIndex >= 0) {
            this.secondaryIndex = this.primaryIndex;
        }
        
        this.primaryIndex = index;
        this.updateAdditionalIndices();
        this.updateDisplay();
    }
    
    moveToSecondary(imageId) {
        const index = this.images.findIndex(img => img.id === imageId);
        if (index === -1) return;
        
        // Check if this image is already secondary
        if (this.secondaryIndex === index) {
            this.showSwapNotification('Image is already secondary');
            return;
        }
        
        // If this image is currently primary, clear the primary position
        if (this.primaryIndex === index) {
            this.primaryIndex = -1;
        }
        
        // If there's already a secondary image, move it to additional
        if (this.secondaryIndex >= 0) {
            // Do nothing, just replace secondary
        }
        
        this.secondaryIndex = index;
        this.updateAdditionalIndices();
        this.updateDisplay();
    }
    
    removeImage(imageId) {
        const index = this.images.findIndex(img => img.id === imageId);
        if (index === -1) return;
        
        // Revoke object URL to free memory
        URL.revokeObjectURL(this.images[index].url);
        
        // Remove from images array
        this.images.splice(index, 1);
        
        // Update indices
        if (this.primaryIndex === index) {
            this.primaryIndex = -1;
        } else if (this.primaryIndex > index) {
            this.primaryIndex--;
        }
        
        if (this.secondaryIndex === index) {
            this.secondaryIndex = -1;
        } else if (this.secondaryIndex > index) {
            this.secondaryIndex--;
        }
        
        // Auto-assign new primary/secondary if needed
        if (this.primaryIndex === -1 && this.images.length > 0) {
            this.primaryIndex = 0;
        }
        if (this.secondaryIndex === -1 && this.images.length > 1) {
            this.secondaryIndex = 1;
        }
        
        this.updateAdditionalIndices();
        this.updateDisplay();
    }
    
    updateCounter() {
        const counter = document.getElementById('imageCounter');
        counter.textContent = `${this.images.length} image${this.images.length !== 1 ? 's' : ''} selected`;
    }
    
    updateHiddenInputs() {
        const container = document.getElementById('hiddenInputs');
        container.innerHTML = '';
        
        // Create ordered array of images
        const orderedImages = [];
        
        // Add primary image first
        if (this.primaryIndex >= 0) {
            orderedImages.push(this.images[this.primaryIndex]);
        }
        
        // Add secondary image second
        if (this.secondaryIndex >= 0) {
            orderedImages.push(this.images[this.secondaryIndex]);
        }
        
        // Add additional images
        this.additionalIndices.forEach(index => {
            orderedImages.push(this.images[index]);
        });
        
        // Create hidden file inputs
        orderedImages.forEach((image, index) => {
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'images[]';
            input.style.display = 'none';
            
            // Create a new FileList-like object
            const dt = new DataTransfer();
            dt.items.add(image.file);
            input.files = dt.files;
            
            container.appendChild(input);
        });
    }
    
    getImages() {
        return this.images;
    }
    
    getOrderedImages() {
        const orderedImages = [];
        
        if (this.primaryIndex >= 0) {
            orderedImages.push(this.images[this.primaryIndex]);
        }
        if (this.secondaryIndex >= 0) {
            orderedImages.push(this.images[this.secondaryIndex]);
        }
        this.additionalIndices.forEach(index => {
            orderedImages.push(this.images[index]);
        });
        
        return orderedImages;
    }
    
    updateSwapButton() {
        const swapBtn = document.getElementById('swapPrimarySecondary');
        if (swapBtn) {
            const canSwap = this.primaryIndex >= 0 && this.secondaryIndex >= 0;
            swapBtn.disabled = !canSwap;
        }
    }
    
    setupDragListeners() {
        // Remove existing listeners
        document.querySelectorAll('.image-item[draggable="true"]').forEach(item => {
            item.removeEventListener('dragstart', this.handleDragStart);
        });
        
        // Add new listeners
        document.querySelectorAll('.image-item[draggable="true"]').forEach(item => {
            item.addEventListener('dragstart', this.handleDragStart.bind(this));
        });
    }
    
    handleDragStart(e) {
        const imageId = e.currentTarget.dataset.imageId;
        e.dataTransfer.setData('text/plain', imageId);
        e.dataTransfer.effectAllowed = 'move';
        
        // Add visual feedback
        e.currentTarget.classList.add('dragging');
        
        // Add a small delay to show the dragging state
        setTimeout(() => {
            e.currentTarget.classList.remove('dragging');
        }, 100);
    }
    
    swapPrimarySecondary() {
        if (this.primaryIndex >= 0 && this.secondaryIndex >= 0) {
            // Add visual feedback
            const swapBtn = document.getElementById('swapPrimarySecondary');
            if (swapBtn) {
                swapBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Swapping...
                `;
                swapBtn.disabled = true;
            }
            
            // Perform the swap
            const temp = this.primaryIndex;
            this.primaryIndex = this.secondaryIndex;
            this.secondaryIndex = temp;
            this.updateAdditionalIndices();
            this.updateDisplay();
            
            // Show success feedback
            setTimeout(() => {
                if (swapBtn) {
                    swapBtn.innerHTML = `
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Swapped! ✓
                    `;
                    swapBtn.style.background = '#10b981';
                }
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    if (swapBtn) {
                        swapBtn.innerHTML = `
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                            </svg>
                            Swap Primary ↔ Secondary
                        `;
                        swapBtn.style.background = '';
                        swapBtn.disabled = false;
                    }
                }, 2000);
            }, 300);
        }
    }
    
    clear() {
        this.images.forEach(image => {
            URL.revokeObjectURL(image.url);
        });
        this.images = [];
        this.primaryIndex = -1;
        this.secondaryIndex = -1;
        this.additionalIndices = [];
        this.updateDisplay();
    }
}

// Initialize the uploader when the page loads
let imageUploader;
document.addEventListener('DOMContentLoaded', function() {
    imageUploader = new AdvancedImageUploader();
});
</script> 