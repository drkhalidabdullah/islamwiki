function removeFromWatchlist(articleId, button) {
    if (!confirm('Remove this article from your watchlist?')) {
        return;
    }
    
    fetch('/api/ajax/watchlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            article_id: articleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the item from the page
            button.closest('.watchlist-item').remove();
            showToast('Removed from watchlist', 'success');
            
            // Check if watchlist is now empty
            const remainingItems = document.querySelectorAll('.watchlist-item');
            if (remainingItems.length === 0) {
                location.reload(); // Reload to show empty state
            }
        } else {
            showToast(data.message || 'Error removing from watchlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing from watchlist', 'error');
    });
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Style the toast
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
        padding: 12px 20px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1000;
        font-size: 14px;
        max-width: 300px;
    `;
    
    document.body.appendChild(toast);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}
