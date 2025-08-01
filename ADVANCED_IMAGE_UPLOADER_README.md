# Advanced Image Uploader

A comprehensive image uploader component for product creation that allows multiple image selection, re-selection, and intelligent ordering of the first two images (Primary and Secondary).

## 🚀 Features

### ✅ Core Functionality
- **Multiple Image Selection**: Select multiple images without losing previously selected ones
- **Re-selection Support**: Add more images later without overwriting existing ones
- **Primary & Secondary Ordering**: First two images can be reordered via drag & drop or manual buttons
- **Additional Images**: Rest of the images are added after the first two with no special ordering
- **Drag & Drop**: Drag primary/secondary images to swap their positions
- **Visual Feedback**: Clear indication of primary (yellow), secondary (blue), and additional (gray) images

### 🎨 User Experience
- **Intuitive Interface**: Clear visual hierarchy and user-friendly controls
- **Real-time Preview**: Instant preview of selected images with proper categorization
- **Hover Actions**: Action buttons appear on hover for easy image management
- **Order Badges**: Visual indicators showing the order of each image
- **Image Counter**: Shows total number of selected images

### 🔧 Technical Features
- **Memory Management**: Proper cleanup of object URLs to prevent memory leaks
- **Form Integration**: Seamlessly integrates with existing product creation forms
- **File Validation**: Validates image file types and sizes
- **Responsive Design**: Works on desktop and mobile devices

## 📁 File Structure

```
admin/
├── advanced_image_uploader.php    # Main uploader component
├── products.php                   # Updated with new uploader
└── ...

test_advanced_image_uploader.php   # Test page for demonstration
ADVANCED_IMAGE_UPLOADER_README.md  # This documentation
```

## 🛠️ Implementation

### 1. Integration with Products Page

The advanced image uploader is integrated into the product creation form by replacing the old image upload section:

```php
<!-- Old implementation -->
<div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
    <input type="file" name="images[]" multiple accept="image/*">
    <div id="imagePreview" class="mt-4 grid grid-cols-4 gap-2"></div>
</div>

<!-- New implementation -->
<?php include 'advanced_image_uploader.php'; ?>
```

### 2. JavaScript Class Structure

The uploader is built around the `AdvancedImageUploader` class:

```javascript
class AdvancedImageUploader {
    constructor() {
        this.images = [];           // Array of image objects
        this.primaryIndex = -1;     // Index of primary image
        this.secondaryIndex = -1;   // Index of secondary image
        this.additionalIndices = []; // Indices of additional images
    }
}
```

### 3. Image Object Structure

Each image is stored as an object with the following properties:

```javascript
{
    file: File,           // The actual file object
    id: number,          // Unique identifier
    url: string          // Object URL for preview
}
```

## 🎯 Usage Instructions

### For Users

1. **Upload Images**
   - Click the upload area or drag & drop images
   - Select multiple images at once or add more later
   - Images are automatically categorized as primary, secondary, or additional

2. **Reorder Primary & Secondary**
   - Drag the primary (yellow) or secondary (blue) images to swap positions
   - Use the "Swap Primary ↔ Secondary" button for manual swapping
   - Only the first two images can be reordered

3. **Manage Additional Images**
   - Additional images (gray) can be moved to primary or secondary positions
   - Use the action buttons that appear on hover
   - Additional images maintain their relative order

4. **Remove Images**
   - Click the trash icon on any image to remove it
   - System automatically reassigns primary/secondary if needed

### For Developers

1. **Include the Component**
   ```php
   <?php include 'admin/advanced_image_uploader.php'; ?>
   ```

2. **Access the Uploader Instance**
   ```javascript
   // The uploader is automatically initialized as 'imageUploader'
   const images = imageUploader.getImages();
   const orderedImages = imageUploader.getOrderedImages();
   ```

3. **Form Submission**
   - The uploader automatically creates hidden file inputs
   - Images are submitted in the correct order: Primary → Secondary → Additional
   - No changes needed to existing form processing logic

## 🎨 Visual Design

### Color Coding
- **Primary Image**: Yellow border and background (`#fef3c7`)
- **Secondary Image**: Blue border and background (`#dbeafe`)
- **Additional Images**: Gray border and background (`#f9fafb`)

### Interactive Elements
- **Hover Effects**: Images scale slightly and show action buttons
- **Drag Handles**: Primary and secondary images show drag handles (↕)
- **Order Badges**: Each image shows its position number
- **Action Buttons**: Move, remove, and swap buttons with hover effects

## 🔧 Technical Details

### Event Handling
- **File Selection**: Handles both click-to-upload and drag & drop
- **Image Reordering**: Drag & drop between primary and secondary containers
- **Button Actions**: Manual reordering and image removal
- **Form Integration**: Automatic creation of hidden file inputs

### Memory Management
- **Object URLs**: Properly created and revoked to prevent memory leaks
- **Event Listeners**: Cleaned up when images are removed
- **DOM Elements**: Efficiently updated without unnecessary re-renders

### Browser Compatibility
- **Modern Browsers**: Full support for drag & drop and File API
- **Mobile Devices**: Touch-friendly interface with fallback to file input
- **Progressive Enhancement**: Works even if JavaScript is disabled

## 🧪 Testing

### Test Page
Use `test_advanced_image_uploader.php` to test all functionality:

1. **Upload multiple images**
2. **Test drag & drop reordering**
3. **Test manual button reordering**
4. **Test image removal**
5. **Test form submission**

### Console Logging
The uploader includes comprehensive console logging for debugging:

```javascript
console.log('Uploading image for content key:', contentKey);
console.log('Image upload response:', data);
console.log('Updated image src to:', '../' + data.file_path);
```

## 🔄 Migration from Old System

### What Changed
- **File Input**: Replaced simple file input with advanced component
- **Preview System**: Replaced basic grid preview with categorized sections
- **JavaScript**: Replaced `setupImagePreview` with `AdvancedImageUploader` class

### What Stayed the Same
- **Form Submission**: Images are still submitted as `images[]` array
- **Backend Processing**: No changes needed to server-side image processing
- **Database Storage**: Same structure for storing image paths and order

### Backward Compatibility
- The new uploader is fully backward compatible
- Existing product creation logic continues to work
- No database schema changes required

## 🚀 Future Enhancements

### Potential Improvements
1. **Image Cropping**: Add image cropping functionality
2. **Bulk Operations**: Select multiple images for bulk actions
3. **Image Optimization**: Automatic image compression and optimization
4. **Advanced Sorting**: Allow reordering of additional images
5. **Image Validation**: More sophisticated file validation and error handling

### Extensibility
The modular design makes it easy to add new features:
- New image types can be added by extending the `createImageHTML` method
- Additional actions can be added to the action buttons
- Custom validation can be implemented in the `handleFileSelection` method

## 📝 Troubleshooting

### Common Issues

1. **Images not uploading**
   - Check file type restrictions (JPG, PNG, GIF, WEBP)
   - Check file size limits (5MB max)
   - Ensure JavaScript is enabled

2. **Drag & drop not working**
   - Ensure you're dragging primary or secondary images
   - Check browser compatibility
   - Try using the manual swap button instead

3. **Form submission issues**
   - Check that hidden inputs are being created
   - Verify the form has `enctype="multipart/form-data"`
   - Check browser console for errors

### Debug Mode
Enable debug logging by checking the browser console for detailed information about:
- File selection events
- Image upload responses
- Drag & drop operations
- Form submission data

## 📞 Support

For issues or questions about the Advanced Image Uploader:
1. Check the browser console for error messages
2. Test with the provided test page
3. Verify file types and sizes
4. Check browser compatibility

The uploader is designed to be robust and user-friendly while maintaining full compatibility with existing systems. 