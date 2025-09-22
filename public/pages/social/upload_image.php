<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Upload Image';
check_maintenance_mode();
require_login();

// Check if social features are enabled
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    show_message('Social features are currently disabled.', 'error');
    redirect('/dashboard');
}

$current_user = get_user($_SESSION['user_id']);
include "../../includes/header.php";
?>

<div class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Upload Image</h1>
            <p>Upload an image to share with your friends</p>
        </div>

        <div class="upload-container">
            <div class="upload-area" id="uploadArea">
                <div class="upload-content">
                    <i class="iw iw-cloud-upload upload-icon"></i>
                    <h3>Drag & Drop Image Here</h3>
                    <p>or <button type="button" class="btn-link" id="selectFileBtn">click to browse</button></p>
                    <p class="upload-info">Supports JPEG, PNG, GIF, and WebP (max 2MB)</p>
                </div>
                <input type="file" id="fileInput" accept="image/*" style="display: none;">
            </div>

            <div class="upload-preview" id="uploadPreview" style="display: none;">
                <div class="preview-image-container">
                    <img id="previewImage" src="" alt="Preview">
                    <button type="button" class="remove-image-btn" id="removeImageBtn">
                        <i class="iw iw-times"></i>
                    </button>
                </div>
                <div class="preview-info">
                    <div class="file-info">
                        <span class="file-name" id="fileName"></span>
                        <span class="file-size" id="fileSize"></span>
                    </div>
                    <div class="upload-actions">
                        <button type="button" class="btn btn-primary" id="uploadBtn">
                            <i class="iw iw-upload"></i>
                            Upload Image
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <div class="upload-progress" id="uploadProgress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p class="progress-text" id="progressText">Uploading...</p>
            </div>

            <div class="upload-result" id="uploadResult" style="display: none;">
                <div class="result-content">
                    <i class="iw iw-check-circle result-icon success"></i>
                    <h3>Upload Successful!</h3>
                    <p>Your image has been uploaded successfully.</p>
                    <div class="result-actions">
                        <button type="button" class="btn btn-primary" id="viewImageBtn">
                            <i class="iw iw-eye"></i>
                            View Image
                        </button>
                        <button type="button" class="btn btn-secondary" id="uploadAnotherBtn">
                            <i class="iw iw-plus"></i>
                            Upload Another
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #2563eb;
    background: #f0f4ff;
}

.upload-area.dragover {
    border-color: #2563eb;
    background: #e6f2ff;
    transform: scale(1.02);
}

.upload-content {
    pointer-events: none;
}

.upload-icon {
    font-size: 48px;
    color: #666;
    margin-bottom: 16px;
}

.upload-content h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 20px;
}

.upload-content p {
    margin: 8px 0;
    color: #666;
}

.btn-link {
    background: none;
    border: none;
    color: #2563eb;
    text-decoration: underline;
    cursor: pointer;
    font-size: inherit;
}

.btn-link:hover {
    color: #1d4ed8;
}

.upload-info {
    font-size: 14px;
    color: #999;
    margin-top: 16px;
}

.upload-preview {
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 20px;
    background: #fff;
    margin-top: 20px;
}

.preview-image-container {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
}

.preview-image-container img {
    max-width: 300px;
    max-height: 300px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.remove-image-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #ff4444;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.remove-image-btn:hover {
    background: #cc0000;
}

.preview-info {
    text-align: center;
}

.file-info {
    margin-bottom: 16px;
}

.file-name {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.file-size {
    display: block;
    color: #666;
    font-size: 14px;
}

.upload-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.upload-progress {
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 20px;
    background: #fff;
    margin-top: 20px;
    text-align: center;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 12px;
}

.progress-fill {
    height: 100%;
    background: #2563eb;
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.upload-result {
    border: 1px solid #4ade80;
    border-radius: 12px;
    padding: 20px;
    background: #f0fdf4;
    margin-top: 20px;
    text-align: center;
}

.result-content h3 {
    margin: 8px 0;
    color: #166534;
}

.result-content p {
    margin: 8px 0 16px 0;
    color: #166534;
}

.result-icon {
    font-size: 48px;
    color: #4ade80;
    margin-bottom: 8px;
}

.result-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.error-message {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 12px;
    border-radius: 6px;
    margin-top: 16px;
    text-align: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const selectFileBtn = document.getElementById('selectFileBtn');
    const uploadPreview = document.getElementById('uploadPreview');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const uploadBtn = document.getElementById('uploadBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    const uploadResult = document.getElementById('uploadResult');
    const viewImageBtn = document.getElementById('viewImageBtn');
    const uploadAnotherBtn = document.getElementById('uploadAnotherBtn');

    let selectedFile = null;
    let uploadedImageUrl = null;

    // File selection
    selectFileBtn.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', handleFileSelect);

    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    function handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            handleFile(file);
        }
    }

    function handleFile(file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            showError('Please select an image file.');
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            showError('File size must be less than 2MB.');
            return;
        }

        selectedFile = file;
        
        // Show preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            
            uploadArea.style.display = 'none';
            uploadPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        uploadArea.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    // Remove image
    removeImageBtn.addEventListener('click', () => {
        selectedFile = null;
        uploadPreview.style.display = 'none';
        uploadArea.style.display = 'block';
        fileInput.value = '';
    });

    // Cancel upload
    cancelBtn.addEventListener('click', () => {
        selectedFile = null;
        uploadPreview.style.display = 'none';
        uploadArea.style.display = 'block';
        fileInput.value = '';
    });

    // Upload image
    uploadBtn.addEventListener('click', () => {
        if (!selectedFile) return;

        const formData = new FormData();
        formData.append('image', selectedFile);

        // Show progress
        uploadPreview.style.display = 'none';
        uploadProgress.style.display = 'block';
        progressFill.style.width = '0%';
        progressText.textContent = 'Uploading...';

        // Simulate progress (since we can't track real progress with fetch)
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressFill.style.width = progress + '%';
        }, 200);

        fetch('/api/ajax/upload_image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressFill.style.width = '100%';
            progressText.textContent = 'Complete!';

            setTimeout(() => {
                if (data.success) {
                    uploadedImageUrl = data.url;
                    showUploadResult(data);
                } else {
                    showError(data.message || 'Upload failed. Please try again.');
                    uploadProgress.style.display = 'none';
                    uploadPreview.style.display = 'block';
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(progressInterval);
            console.error('Upload error:', error);
            showError('Upload failed. Please try again.');
            uploadProgress.style.display = 'none';
            uploadPreview.style.display = 'block';
        });
    });

    function showUploadResult(data) {
        uploadProgress.style.display = 'none';
        uploadResult.style.display = 'block';
    }

    // View uploaded image
    viewImageBtn.addEventListener('click', () => {
        if (uploadedImageUrl) {
            window.open(uploadedImageUrl, '_blank');
        }
    });

    // Upload another image
    uploadAnotherBtn.addEventListener('click', () => {
        selectedFile = null;
        uploadedImageUrl = null;
        uploadResult.style.display = 'none';
        uploadArea.style.display = 'block';
        fileInput.value = '';
    });
});
</script>

<?php include "../../includes/footer.php"; ?>
