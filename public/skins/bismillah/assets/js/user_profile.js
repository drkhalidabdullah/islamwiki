document.addEventListener('DOMContentLoaded', function() {
    // Follow/Unfollow functionality
    const followBtn = document.querySelector('.follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const isFollowing = this.dataset.following === 'true';
            
            fetch('ajax/follow_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    action: isFollowing ? 'unfollow' : 'follow'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.following = isFollowing ? 'false' : 'true';
                    this.textContent = isFollowing ? 'Follow' : 'Following';
                    
                    // Update follower count
                    const followerStat = document.querySelector('.stat-item:first-child .stat-number');
                    if (followerStat) {
                        const currentCount = parseInt(followerStat.textContent.replace(/,/g, ''));
                        const newCount = isFollowing ? currentCount - 1 : currentCount + 1;
                        followerStat.textContent = newCount.toLocaleString();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
    // Like/Unlike functionality
    const likeBtns = document.querySelectorAll('.like-btn');
    likeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const isLiked = this.dataset.liked === 'true';
            
            fetch('ajax/like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: postId,
                    action: isLiked ? 'unlike' : 'like'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.liked = isLiked ? 'false' : 'true';
                    this.classList.toggle('liked', !isLiked);
                    
                    // Update like count
                    const countSpan = this.querySelector('.count');
                    if (countSpan) {
                        const currentCount = parseInt(countSpan.textContent.replace(/,/g, ''));
                        const newCount = isLiked ? currentCount - 1 : currentCount + 1;
                        countSpan.textContent = newCount.toLocaleString();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    // Profile Picture Modal functionality
    window.selectedImage = null;
    window.currentZoom = 1;
    window.currentPosition = { x: 0, y: 0 };
    window.isDragging = false;
    window.dragStart = { x: 0, y: 0 };
});

// Profile Picture Modal Functions
function openProfilePictureModal() {
    const modal = document.getElementById('profilePictureModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Reset to initial options
    showInitialOptions();
}

function closeProfilePictureModal() {
    const modal = document.getElementById('profilePictureModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Reset all states
    showInitialOptions();
    window.selectedImage = null;
    window.currentZoom = 1;
    window.currentPosition = { x: 0, y: 0 };
}

function showInitialOptions() {
    document.getElementById('initialOptions').style.display = 'block';
    document.getElementById('pictureSelection').style.display = 'none';
    document.getElementById('thumbnailAdjustment').style.display = 'none';
}

function showProfilePictureViewer() {
    console.log('Opening profile picture viewer...');
    
    // Show the current profile picture in full screen
    const currentAvatar = document.querySelector('.profile-picture-container .profile-picture, .profile-picture-container img, .profile-avatar img');
    console.log('Found current avatar:', currentAvatar);
    
    if (currentAvatar) {
        const viewer = document.getElementById('profilePictureViewer');
        const viewerImage = document.getElementById('viewerImage');
        
        if (currentAvatar.tagName === 'IMG') {
            console.log('Setting viewer image to:', currentAvatar.src);
            viewerImage.src = currentAvatar.src;
            viewerImage.style.display = 'block';
        } else {
            // For avatar circles, we'll show a placeholder or the current profile picture
            const profileImg = document.querySelector('.profile-picture-container img');
            if (profileImg) {
                console.log('Found profile img for circle:', profileImg.src);
                viewerImage.src = profileImg.src;
                viewerImage.style.display = 'block';
            } else {
                // Show a placeholder or the user's initials
                console.log('No profile image found, hiding viewer image');
                viewerImage.style.display = 'none';
                // You could add logic here to show initials or a default image
            }
        }
        
        // Load comments for this profile picture
        loadProfilePictureComments();
        
        // Show the viewer
        console.log('Showing viewer, current display:', viewer.style.display);
        viewer.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        console.log('Viewer display set to:', viewer.style.display);
        console.log('Viewer element:', viewer);
        
        // Close the modal
        closeProfilePictureModal();
    } else {
        console.log('No current avatar found!');
        showNotification('Profile picture not found', 'error');
    }
}

function closeProfilePictureViewer() {
    const viewer = document.getElementById('profilePictureViewer');
    viewer.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Add ESC key functionality for profile picture viewer
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const viewer = document.getElementById('profilePictureViewer');
        if (viewer && viewer.style.display === 'flex') {
            closeProfilePictureViewer();
        }
        
        const modal = document.getElementById('profilePictureModal');
        if (modal && modal.style.display === 'flex') {
            closeProfilePictureModal();
        }
    }
});

function loadProfilePictureComments() {
    // Load comments for the current profile picture
    // This would typically make an API call to get comments
    const commentsContainer = document.getElementById('viewerComments');
    commentsContainer.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">No comments yet.</p>';
}

function submitComment() {
    const commentInput = document.getElementById('commentInput');
    const comment = commentInput.value.trim();
    
    if (comment) {
        // This would typically make an API call to submit the comment
        console.log('Comment submitted:', comment);
        commentInput.value = '';
        // You could add the comment to the UI here
    }
}

function showPictureSelection() {
    document.getElementById('initialOptions').style.display = 'none';
    document.getElementById('pictureSelection').style.display = 'block';
    
    // Load photos
    loadSuggestedPhotos();
    loadUserUploads();
    loadProfilePictures();
    loadCoverPhotos();
}

function triggerFileUpload() {
    document.getElementById('profilePictureUpload').click();
}

function handleFileUpload(input) {
    const file = input.files[0];
    if (file) {
        // Check if user wants to skip adjustment (hold Shift key)
        if (event.shiftKey) {
            console.log('Direct upload mode - skipping adjustment');
            uploadFileDirectly(file);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            window.selectedImage = e.target.result;
            showThumbnailAdjustment();
        };
        reader.readAsDataURL(file);
    }
}

function uploadFileDirectly(file) {
    // Prevent multiple simultaneous uploads
    if (window.isUploading) {
        console.log('Upload already in progress, ignoring duplicate call');
        return;
    }
    window.isUploading = true;
    
    // Show loading state
    showNotification('Uploading profile picture directly...', 'info');
    
    const formData = new FormData();
    formData.append('profile_picture', file);
    
    fetch('/api/update_profile_picture.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        console.log('Server response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Parsed data:', data);
            if (data.success) {
                console.log('Success! New avatar URL:', data.new_avatar_url);
                
                // Show success notification
                showNotification('Profile picture updated successfully!', 'success');
                
                // Update profile pictures site-wide with server timestamp
                updateProfilePictureSiteWide(data.new_avatar_url, data.timestamp);
                
                // Close modal after a short delay to show the success
                setTimeout(() => {
                    closeProfilePictureModal();
                }, 1000);
            } else {
                console.error('Server returned error:', data.message);
                showNotification('Failed to update profile picture: ' + data.message, 'error');
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', text);
            showNotification('Failed to parse server response', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update profile picture: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset upload flag
        window.isUploading = false;
    });
}

function showThumbnailAdjustment() {
    if (!window.selectedImage) return;
    
    document.getElementById('pictureSelection').style.display = 'none';
    document.getElementById('thumbnailAdjustment').style.display = 'block';
    
    const img = document.getElementById('adjustmentImage');
    img.src = window.selectedImage;
    
    // Reset zoom and position
    window.currentZoom = 1;
    window.currentPosition = { x: 0, y: 0 };
    updateImageTransform();
}

function adjustZoom(zoom) {
    window.currentZoom = parseFloat(zoom);
    document.getElementById('zoomSlider').value = window.currentZoom;
    updateImageTransform();
}

function updateImageTransform() {
    const img = document.getElementById('adjustmentImage');
    img.style.transform = `scale(${window.currentZoom}) translate(${window.currentPosition.x}px, ${window.currentPosition.y}px)`;
}

function cancelThumbnailAdjustment() {
    showPictureSelection();
    window.selectedImage = null;
}

function saveProfilePicture() {
    if (!window.selectedImage) return;
    
    // Prevent multiple simultaneous uploads
    if (window.isUploading) {
        console.log('Upload already in progress, ignoring duplicate call');
        return;
    }
    window.isUploading = true;
    
    // Show loading state
    const saveBtn = document.querySelector('.btn-save');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';
    saveBtn.disabled = true;
    
    // Show loading notification
    showNotification('Uploading profile picture...', 'info');
    
    // Create a canvas to crop the image
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = document.getElementById('adjustmentImage');
    
    // Set canvas size to profile picture size (100x100)
    canvas.width = 100;
    canvas.height = 100;
    
    // Calculate crop area based on zoom and position
    const scale = window.currentZoom || 1;
    const offsetX = window.currentPosition?.x || 0;
    const offsetY = window.currentPosition?.y || 0;
    
    console.log('Scaling parameters:', { scale, offsetX, offsetY, imgWidth: img.naturalWidth, imgHeight: img.naturalHeight });
    
    // Get the displayed image dimensions (the image as shown in the preview)
    const displayedWidth = img.clientWidth;
    const displayedHeight = img.clientHeight;
    
    // Calculate the actual image dimensions when scaled
    const scaledWidth = displayedWidth * scale;
    const scaledHeight = displayedHeight * scale;
    
    // Calculate the crop area (100x100 in the final output)
    const cropSize = 100;
    
    // Calculate the source dimensions in the original image
    const sourceWidth = (img.naturalWidth / scaledWidth) * cropSize;
    const sourceHeight = (img.naturalHeight / scaledHeight) * cropSize;
    
    // Calculate the source position, accounting for the offset and centering
    const centerX = img.naturalWidth / 2;
    const centerY = img.naturalHeight / 2;
    
    // Convert CSS transform offset to image coordinates
    const imageOffsetX = (offsetX / displayedWidth) * img.naturalWidth;
    const imageOffsetY = (offsetY / displayedHeight) * img.naturalHeight;
    
    const sourceX = Math.max(0, Math.min(img.naturalWidth - sourceWidth, centerX - sourceWidth/2 + imageOffsetX));
    const sourceY = Math.max(0, Math.min(img.naturalHeight - sourceHeight, centerY - sourceHeight/2 + imageOffsetY));
    
    console.log('Source dimensions:', { sourceX, sourceY, sourceWidth, sourceHeight, imageOffsetX, imageOffsetY });
    
    // Draw the cropped image
    ctx.drawImage(img, 
        sourceX, sourceY, sourceWidth, sourceHeight,
        0, 0, cropSize, cropSize
    );
    
    // Convert to blob and upload
    canvas.toBlob(function(blob) {
        console.log('Canvas blob created, size:', blob.size);
        const formData = new FormData();
        formData.append('profile_picture', blob, 'profile_picture.jpg');
        
        fetch('/api/update_profile_picture.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            console.log('Server response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                if (data.success) {
                    console.log('Success! New avatar URL:', data.new_avatar_url);
                    console.log('Server timestamp:', data.timestamp);
                    
                    // Show success notification
                    showNotification('Profile picture updated successfully!', 'success');
                    
                    // Update profile pictures site-wide with server timestamp
                    updateProfilePictureSiteWide(data.new_avatar_url, data.timestamp);
                    
                    // Close modal after a short delay to show the success
                    setTimeout(() => {
                        closeProfilePictureModal();
                    }, 1000);
                } else {
                    console.error('Server returned error:', data.message);
                    showNotification('Failed to update profile picture: ' + data.message, 'error');
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                showNotification('Failed to parse server response', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to update profile picture: ' + error.message, 'error');
        })
        .finally(() => {
            // Restore button state
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
            // Reset upload flag
            window.isUploading = false;
        });
    }, 'image/jpeg', 0.9);
}

function loadSuggestedPhotos() {
    // Load a selection of user's uploaded photos as suggested photos
    fetch('/api/get_user_photos.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success && data.photos.length > 0) {
                // Take first 6 photos as suggested
                const suggestedPhotos = data.photos.slice(0, 6);
                const container = document.getElementById('suggestedPhotos');
                container.innerHTML = suggestedPhotos.map(photo => `
                    <div class="photo-item" onclick="selectPhoto('${photo.url}')">
                        <img src="${photo.url}" alt="Suggested photo" onerror="this.style.display='none'">
                    </div>
                `).join('');
            } else {
                // Show placeholder if no photos
                const container = document.getElementById('suggestedPhotos');
                container.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">No photos available</p>';
            }
        } catch (e) {
            console.error('JSON parse error for suggested photos:', e);
            console.error('Response text:', text);
        }
    })
    .catch(error => {
        console.error('Error loading suggested photos:', error);
    });
}

function loadUserUploads(offset = 0, append = false) {
    // Load user's uploaded photos
    fetch(`/api/get_user_photos.php?offset=${offset}&limit=${photosPerPage}`)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                const container = document.getElementById('userUploads');
                const photosHtml = data.photos.map(photo => `
                    <div class="photo-item" onclick="selectPhoto('${photo.url}')">
                        <img src="${photo.url}" alt="User upload">
                    </div>
                `).join('');
                
                if (append) {
                    container.innerHTML += photosHtml;
                } else {
                    container.innerHTML = photosHtml;
                }
                
                // Show/hide "See more" button based on whether there are more photos
                const seeMoreBtn = container.parentElement.querySelector('.see-more-btn');
                if (seeMoreBtn) {
                    seeMoreBtn.style.display = data.photos.length < photosPerPage ? 'none' : 'block';
                }
            }
        } catch (e) {
            console.error('JSON parse error for user uploads:', e);
            console.error('Response text:', text);
        }
    })
    .catch(error => {
        console.error('Error loading user uploads:', error);
    });
}

function loadProfilePictures() {
    // Load user's previous profile pictures
    fetch('/api/get_profile_pictures.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                const container = document.getElementById('profilePictures');
                container.innerHTML = data.photos.map(photo => `
                    <div class="photo-item" onclick="selectPhoto('${photo.url}')">
                        <img src="${photo.url}" alt="Profile picture">
                    </div>
                `).join('');
            }
        } catch (e) {
            console.error('JSON parse error for profile pictures:', e);
            console.error('Response text:', text);
        }
    })
    .catch(error => {
        console.error('Error loading profile pictures:', error);
    });
}

function loadCoverPhotos() {
    // Load user's cover photos
    fetch('/api/get_cover_photos.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                const container = document.getElementById('coverPhotos');
                container.innerHTML = data.photos.map(photo => `
                    <div class="photo-item" onclick="selectPhoto('${photo.url}')">
                        <img src="${photo.url}" alt="Cover photo">
                    </div>
                `).join('');
            }
        } catch (e) {
            console.error('JSON parse error for cover photos:', e);
            console.error('Response text:', text);
        }
    })
    .catch(error => {
        console.error('Error loading cover photos:', error);
    });
}

function selectPhoto(imageUrl) {
    window.selectedImage = imageUrl;
    showThumbnailAdjustment();
}

// Pagination variables
let currentUploadsPage = 0;
let currentCoverPhotosPage = 0;
const photosPerPage = 12;

function loadMoreSuggested() {
    // For suggested photos, we'll just reload the same photos
    loadSuggestedPhotos();
}

function loadMoreUploads() {
    currentUploadsPage++;
    loadUserUploads(currentUploadsPage * photosPerPage, true);
}

function loadMoreCoverPhotos() {
    currentCoverPhotosPage++;
    loadCoverPhotos(currentCoverPhotosPage * photosPerPage, true);
}

function showFrames() {
    // Show frame selection (placeholder)
    showNotification('Frame selection coming soon!', 'info');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => document.body.removeChild(notification), 300);
    }, 3000);
}

// Global function to update profile pictures site-wide
function updateProfilePictureSiteWide(newAvatarUrl, serverTimestamp = null) {
    console.log('Updating profile picture to:', newAvatarUrl);
    console.log('Using server timestamp:', serverTimestamp);
    
    // Get the profile picture container
    const profileContainer = document.querySelector('.profile-picture-container');
    if (!profileContainer) {
        console.error('Profile picture container not found!');
        return;
    }
    
    console.log('Found profile container:', profileContainer);
    
    // Check if there's already an img element
    let profileImg = profileContainer.querySelector('img.profile-picture');
    
    if (profileImg) {
        console.log('Found existing img element, updating...', profileImg);
        // Update existing image
        const newSrc = newAvatarUrl + '?t=' + Date.now();
        profileImg.src = newSrc;
        profileImg.style.display = 'block';
        profileImg.style.visibility = 'visible';
        profileImg.style.opacity = '1';
    } else {
        console.log('No existing img element found, creating new one...');
        
        // Remove any existing avatar circle
        const existingCircle = profileContainer.querySelector('.avatar-circle');
        if (existingCircle) {
            console.log('Removing existing avatar circle:', existingCircle);
            existingCircle.remove();
        }
        
        // Create new img element
        profileImg = document.createElement('img');
        profileImg.className = 'profile-picture';
        profileImg.alt = 'Profile picture';
        profileImg.src = newAvatarUrl + '?t=' + Date.now();
        profileImg.style.display = 'block';
        profileImg.style.visibility = 'visible';
        profileImg.style.opacity = '1';
        profileImg.style.width = '100px';
        profileImg.style.height = '100px';
        profileImg.style.borderRadius = '50%';
        profileImg.style.objectFit = 'cover';
        profileImg.style.border = '4px solid white';
        
        console.log('Created new img element:', profileImg);
        
        // Insert the new image into the container
        profileContainer.insertBefore(profileImg, profileContainer.firstChild);
    }
    
    // Add load/error event listeners
    profileImg.onload = function() {
        console.log('Profile image loaded successfully:', this.src);
        console.log('Image element after load:', this);
        console.log('Image natural dimensions:', this.naturalWidth, 'x', this.naturalHeight);
        
        // Ensure the image is visible with animation
        this.style.display = 'block';
        this.style.visibility = 'visible';
        this.style.opacity = '0';
        this.style.transition = 'opacity 0.3s ease-in-out';
        
        // Fade in the image
        setTimeout(() => {
            this.style.opacity = '1';
        }, 50);
        
        // Hide any remaining avatar circles
        const profileAvatar = this.closest('.profile-avatar');
        if (profileAvatar) {
            const avatarCircles = profileAvatar.querySelectorAll('.avatar-circle');
            console.log('Found avatar circles to hide:', avatarCircles.length);
            avatarCircles.forEach((circle, index) => {
                console.log(`Hiding avatar circle ${index}:`, circle);
                circle.style.display = 'none';
            });
        }
        
        // Add a subtle highlight effect to show the update
        this.style.boxShadow = '0 0 20px rgba(52, 152, 219, 0.5)';
        setTimeout(() => {
            this.style.boxShadow = '';
        }, 2000);
    };
    
    profileImg.onerror = function() {
        console.error('Profile image failed to load:', this.src);
    };
    
    // Update all profile pictures on the page with comprehensive selectors
    const allProfileImages = document.querySelectorAll(`
        .profile-picture-container img,
        .profile-avatar img,
        .author-avatar img,
        .user-avatar img,
        .profile-picture,
        img[src*="profile_"],
        img[src*="avatar"]
    `);
    
    console.log('Found profile images to update:', allProfileImages.length);
    allProfileImages.forEach((img, index) => {
        // Use server timestamp if available, otherwise use current time + index
        const timestamp = serverTimestamp ? serverTimestamp + index : Date.now() + index;
        console.log('Updating profile image:', img, 'from', img.src, 'to', newAvatarUrl);
        
        // Add a loading state
        img.style.opacity = '0.5';
        img.style.transition = 'opacity 0.3s ease-in-out';
        
        // Update the source with cache busting
        img.src = newAvatarUrl + '?t=' + timestamp;
        
        // Restore opacity when loaded
        img.onload = function() {
            this.style.opacity = '1';
        };
    });
    
    // Update avatar circles to show the new image
    const avatarCircles = document.querySelectorAll('.avatar-circle');
    console.log('Found avatar circles:', avatarCircles.length);
    avatarCircles.forEach(circle => {
        // If this circle should show an image, replace it with an img element
        if (circle.closest('.profile-avatar') || circle.closest('.author-avatar') || circle.closest('.profile-picture-container')) {
            const img = document.createElement('img');
            img.src = newAvatarUrl + '?t=' + Date.now();
            img.alt = 'Profile picture';
            img.className = circle.className;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.borderRadius = '50%';
            img.style.objectFit = 'cover';
            circle.parentNode.replaceChild(img, circle);
            console.log('Replaced avatar circle with image:', img);
        }
    });
    
    // Store the new avatar URL in localStorage for other pages
    localStorage.setItem('user_avatar_url', newAvatarUrl);
    localStorage.setItem('user_avatar_updated', (serverTimestamp || Date.now()).toString());
    
    console.log('Profile picture update complete');
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('profilePictureModal');
    if (e.target === modal) {
        closeProfilePictureModal();
    }
});

// Handle drag functionality for image repositioning
document.addEventListener('DOMContentLoaded', function() {
    const profilePreview = document.querySelector('.profile-preview');
    if (profilePreview) {
        profilePreview.addEventListener('mousedown', startDrag);
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', endDrag);
    }
});

function startDrag(e) {
    if (e.target.tagName === 'IMG') {
        window.isDragging = true;
        window.dragStart = { x: e.clientX - window.currentPosition.x, y: e.clientY - window.currentPosition.y };
        e.preventDefault();
    }
}

function drag(e) {
    if (window.isDragging) {
        window.currentPosition.x = e.clientX - window.dragStart.x;
        window.currentPosition.y = e.clientY - window.dragStart.y;
        updateImageTransform();
    }
}

function endDrag() {
    window.isDragging = false;
}
