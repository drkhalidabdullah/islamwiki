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
});
