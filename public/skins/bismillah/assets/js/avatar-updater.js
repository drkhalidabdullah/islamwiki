// Global avatar updater script
// This script should be included on all pages to ensure profile pictures update site-wide

document.addEventListener('DOMContentLoaded', function() {
    console.log('Avatar updater: DOM loaded, checking for avatar updates...');
    
    // Check if there's a new avatar URL in localStorage
    const newAvatarUrl = localStorage.getItem('user_avatar_url');
    const avatarUpdated = localStorage.getItem('user_avatar_updated');
    
    console.log('Avatar updater: Found avatar URL:', newAvatarUrl);
    console.log('Avatar updater: Found avatar updated time:', avatarUpdated);
    
    if (newAvatarUrl && avatarUpdated) {
        // Check if the update was recent (within the last 5 minutes)
        const updateTime = parseInt(avatarUpdated);
        const now = Date.now();
        const fiveMinutes = 5 * 60 * 1000;
        
        console.log('Avatar updater: Update time:', updateTime, 'Now:', now, 'Difference:', now - updateTime);
        
        if (now - updateTime < fiveMinutes) {
            console.log('Avatar updater: Update is recent, updating profile pictures...');
            // Update all profile pictures on this page
            updateProfilePicturesOnPage(newAvatarUrl);
        } else {
            console.log('Avatar updater: Update is too old, skipping...');
        }
    } else {
        console.log('Avatar updater: No avatar update found in localStorage');
    }
});

function updateProfilePicturesOnPage(newAvatarUrl) {
    console.log('Avatar updater: Updating profile pictures to:', newAvatarUrl);
    
    // Get the profile picture container
    const profileContainer = document.querySelector('.profile-picture-container');
    if (!profileContainer) {
        console.log('Avatar updater: Profile picture container not found');
        return;
    }
    
    // Check if there's already an img element
    let profileImg = profileContainer.querySelector('img.profile-picture');
    
    if (profileImg) {
        console.log('Avatar updater: Found existing img element, updating...', profileImg);
        // Update existing image
        profileImg.src = newAvatarUrl + '?t=' + Date.now();
        profileImg.style.display = 'block';
        profileImg.style.visibility = 'visible';
        profileImg.style.opacity = '1';
    } else {
        console.log('Avatar updater: No existing img element found, creating new one...');
        
        // Remove any existing avatar circle
        const existingCircle = profileContainer.querySelector('.avatar-circle');
        if (existingCircle) {
            console.log('Avatar updater: Removing existing avatar circle:', existingCircle);
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
        
        console.log('Avatar updater: Created new img element:', profileImg);
        
        // Insert the new image into the container
        profileContainer.insertBefore(profileImg, profileContainer.firstChild);
    }
    
    // Add load event listener
    profileImg.onload = function() {
        console.log('Avatar updater: Profile image loaded successfully:', this.src);
        
        // Ensure the image is visible
        this.style.display = 'block';
        this.style.visibility = 'visible';
        this.style.opacity = '1';
        
        // Hide any remaining avatar circles
        const profileAvatar = this.closest('.profile-avatar');
        if (profileAvatar) {
            const avatarCircles = profileAvatar.querySelectorAll('.avatar-circle');
            console.log('Avatar updater: Found avatar circles to hide:', avatarCircles.length);
            avatarCircles.forEach((circle, index) => {
                console.log(`Avatar updater: Hiding avatar circle ${index}:`, circle);
                circle.style.display = 'none';
            });
        }
    };
    
    // Update all profile pictures on the page with comprehensive selectors
    const allProfileImages = document.querySelectorAll(`
        .profile-picture-container img,
        .profile-avatar img,
        .author-avatar img,
        .user-avatar img,
        .profile-picture,
        .user-avatar-img,
        img[src*="profile_"],
        img[src*="avatar"]
    `);
    
    console.log('Avatar updater: Found profile images to update:', allProfileImages.length);
    allProfileImages.forEach((img, index) => {
        const timestamp = Date.now() + index; // Add index to ensure unique timestamps
        console.log('Avatar updater: Updating profile image:', img, 'from', img.src, 'to', newAvatarUrl);
        
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
    console.log('Avatar updater: Found avatar circles:', avatarCircles.length);
    avatarCircles.forEach(circle => {
        // If this circle should show an image, replace it with an img element
        if (circle.closest('.profile-avatar') || 
            circle.closest('.author-avatar') || 
            circle.closest('.user-avatar') ||
            circle.closest('.avatar') ||
            circle.closest('.profile-picture-container')) {
            const img = document.createElement('img');
            img.src = newAvatarUrl + '?t=' + Date.now();
            img.alt = 'Profile picture';
            img.className = circle.className;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.borderRadius = '50%';
            img.style.objectFit = 'cover';
            circle.parentNode.replaceChild(img, circle);
            console.log('Avatar updater: Replaced avatar circle with image:', img);
        }
    });
    
    console.log('Avatar updater: Profile picture update complete');
}

// Listen for storage events to update avatars across tabs
window.addEventListener('storage', function(e) {
    if (e.key === 'user_avatar_url' && e.newValue) {
        updateProfilePicturesOnPage(e.newValue);
    }
});
