// Photos Gallery JavaScript - Facebook-inspired functionality

document.addEventListener('DOMContentLoaded', function() {
    const photosGrid = document.getElementById('photosGrid');
    const photoModal = document.getElementById('photoModal');
    const modalPhoto = document.getElementById('modalPhoto');
    const modalCaption = document.getElementById('modalCaption');
    const modalDate = document.getElementById('modalDate');
    const modalLikes = document.getElementById('modalLikes');
    const modalComments = document.getElementById('modalComments');
    const modalShares = document.getElementById('modalShares');
    const closeModal = document.getElementById('closeModal');
    const prevPhoto = document.getElementById('prevPhoto');
    const nextPhoto = document.getElementById('nextPhoto');
    
    let currentPhotoIndex = 0;
    let photos = [];
    
    // Initialize photos array from DOM
    if (photosGrid) {
        const photoItems = photosGrid.querySelectorAll('.photo-item');
        photos = Array.from(photoItems).map(item => ({
            id: item.dataset.photoId,
            index: parseInt(item.dataset.index),
            src: item.querySelector('img').src,
            caption: item.querySelector('.photo-caption')?.textContent || '',
            date: item.querySelector('.photo-date')?.textContent || '',
            likes: item.querySelector('.like-btn span')?.textContent || '0',
            comments: item.querySelector('.comment-btn span')?.textContent || '0',
            shares: item.querySelector('.share-btn span')?.textContent || '0'
        }));
    }
    
    // Photo grid click handlers
    if (photosGrid) {
        photosGrid.addEventListener('click', function(e) {
            const photoItem = e.target.closest('.photo-item');
            if (photoItem) {
                const photoIndex = parseInt(photoItem.dataset.index);
                openPhotoModal(photoIndex);
            }
        });
    }
    
    // Modal controls
    if (closeModal) {
        closeModal.addEventListener('click', closePhotoModal);
    }
    
    if (prevPhoto) {
        prevPhoto.addEventListener('click', showPreviousPhoto);
    }
    
    if (nextPhoto) {
        nextPhoto.addEventListener('click', showNextPhoto);
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (photoModal.classList.contains('show')) {
            switch(e.key) {
                case 'Escape':
                    closePhotoModal();
                    break;
                case 'ArrowLeft':
                    showPreviousPhoto();
                    break;
                case 'ArrowRight':
                    showNextPhoto();
                    break;
            }
        }
    });
    
    // Click outside modal to close
    if (photoModal) {
        photoModal.addEventListener('click', function(e) {
            if (e.target === photoModal) {
                closePhotoModal();
            }
        });
    }
    
    // Action button handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.like-btn')) {
            e.stopPropagation();
            handleLike(e.target.closest('.like-btn'));
        } else if (e.target.closest('.comment-btn')) {
            e.stopPropagation();
            handleComment(e.target.closest('.comment-btn'));
        } else if (e.target.closest('.share-btn')) {
            e.stopPropagation();
            handleShare(e.target.closest('.share-btn'));
        }
    });
    
    // Follow button handler
    const followBtn = document.querySelector('.follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', handleFollow);
    }
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    function openPhotoModal(index) {
        if (photos.length === 0) return;
        
        currentPhotoIndex = index;
        updateModalContent();
        photoModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    function closePhotoModal() {
        photoModal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    function showPreviousPhoto() {
        if (photos.length === 0) return;
        
        currentPhotoIndex = (currentPhotoIndex - 1 + photos.length) % photos.length;
        updateModalContent();
    }
    
    function showNextPhoto() {
        if (photos.length === 0) return;
        
        currentPhotoIndex = (currentPhotoIndex + 1) % photos.length;
        updateModalContent();
    }
    
    function updateModalContent() {
        if (photos.length === 0) return;
        
        const photo = photos[currentPhotoIndex];
        
        if (modalPhoto) {
            modalPhoto.src = photo.src;
            modalPhoto.alt = photo.caption || 'Photo';
        }
        
        if (modalCaption) {
            modalCaption.textContent = photo.caption || '';
        }
        
        if (modalDate) {
            modalDate.textContent = photo.date || '';
        }
        
        if (modalLikes) {
            modalLikes.textContent = photo.likes || '0';
        }
        
        if (modalComments) {
            modalComments.textContent = photo.comments || '0';
        }
        
        if (modalShares) {
            modalShares.textContent = photo.shares || '0';
        }
        
        // Update navigation button states
        if (prevPhoto) {
            prevPhoto.style.display = photos.length > 1 ? 'flex' : 'none';
        }
        
        if (nextPhoto) {
            nextPhoto.style.display = photos.length > 1 ? 'flex' : 'none';
        }
    }
    
    function handleLike(button) {
        const photoId = button.dataset.photoId;
        const isLiked = button.classList.contains('liked');
        
        // Optimistic update
        const likeCount = button.querySelector('span');
        const currentCount = parseInt(likeCount.textContent.replace(/,/g, ''));
        likeCount.textContent = isLiked ? 
            formatNumber(currentCount - 1) : 
            formatNumber(currentCount + 1);
        
        button.classList.toggle('liked');
        
        // Update modal if open
        if (photoModal.classList.contains('show')) {
            const modalLikeCount = document.getElementById('modalLikes');
            if (modalLikeCount) {
                modalLikeCount.textContent = likeCount.textContent;
            }
        }
        
        // Send request to server
        fetch('/api/ajax/like_photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                photo_id: photoId,
                action: isLiked ? 'unlike' : 'like'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert optimistic update
                likeCount.textContent = formatNumber(currentCount);
                button.classList.toggle('liked');
            }
        })
        .catch(error => {
            console.error('Error liking photo:', error);
            // Revert optimistic update
            likeCount.textContent = formatNumber(currentCount);
            button.classList.toggle('liked');
        });
    }
    
    function handleComment(button) {
        const photoId = button.dataset.photoId;
        // TODO: Implement comment functionality
        console.log('Comment on photo:', photoId);
    }
    
    function handleShare(button) {
        const photoId = button.dataset.photoId;
        // TODO: Implement share functionality
        console.log('Share photo:', photoId);
    }
    
    function handleFollow() {
        const userId = this.dataset.userId;
        const isFollowing = this.dataset.following === 'true';
        
        // Optimistic update
        this.textContent = isFollowing ? 'Follow' : 'Following';
        this.dataset.following = (!isFollowing).toString();
        
        // Send request to server
        fetch('/api/ajax/follow_user.php', {
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
            if (!data.success) {
                // Revert optimistic update
                this.textContent = isFollowing ? 'Following' : 'Follow';
                this.dataset.following = isFollowing.toString();
            }
        })
        .catch(error => {
            console.error('Error following user:', error);
            // Revert optimistic update
            this.textContent = isFollowing ? 'Following' : 'Follow';
            this.dataset.following = isFollowing.toString();
        });
    }
    
    function formatNumber(num) {
        return num.toLocaleString();
    }
    
    // Touch/swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    if (photoModal) {
        photoModal.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        photoModal.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
    }
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                // Swipe left - next photo
                showNextPhoto();
            } else {
                // Swipe right - previous photo
                showPreviousPhoto();
            }
        }
    }
    
    // Preload adjacent images for better performance
    function preloadAdjacentImages() {
        if (photos.length <= 1) return;
        
        const prevIndex = (currentPhotoIndex - 1 + photos.length) % photos.length;
        const nextIndex = (currentPhotoIndex + 1) % photos.length;
        
        [prevIndex, nextIndex].forEach(index => {
            if (index !== currentPhotoIndex) {
                const img = new Image();
                img.src = photos[index].src;
            }
        });
    }
    
    // Call preload when modal opens
    const originalOpenModal = openPhotoModal;
    openPhotoModal = function(index) {
        originalOpenModal(index);
        preloadAdjacentImages();
    };
});

// Utility functions for external use
window.PhotosGallery = {
    openModal: function(photoIndex) {
        const event = new CustomEvent('openPhotoModal', { detail: { index: photoIndex } });
        document.dispatchEvent(event);
    },
    
    closeModal: function() {
        const event = new CustomEvent('closePhotoModal');
        document.dispatchEvent(event);
    }
};
