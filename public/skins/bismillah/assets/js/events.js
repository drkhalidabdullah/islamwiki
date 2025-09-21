// Events Page JavaScript

// Open create event modal
function openCreateEventModal() {
    const modal = document.getElementById('createEventModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Set default start date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('event_start_date').value = today;
}

// Close create event modal
function closeCreateEventModal() {
    const modal = document.getElementById('createEventModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.getElementById('createEventForm').reset();
}

// Handle form submission
document.getElementById('createEventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    submitBtn.disabled = true;
    
    fetch('/api/ajax/create_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Event created successfully!', 'success');
            closeCreateEventModal();
            
            // Reload page to show new event
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to create event.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while creating the event.', 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Toggle event attendance
function toggleAttendance(eventId) {
    const button = document.querySelector(`[data-event-id="${eventId}"] .attend-btn, [data-event-id="${eventId}"] .attending-btn`);
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    button.disabled = true;
    
    const formData = new FormData();
    formData.append('event_id', eventId);
    
    fetch('/api/ajax/toggle_event_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button state
            if (data.attending) {
                button.className = 'btn btn-secondary attending-btn';
                button.innerHTML = '<i class="fas fa-check"></i> Going';
                button.onclick = () => toggleAttendance(eventId);
            } else {
                button.className = 'btn btn-primary attend-btn';
                button.innerHTML = '<i class="fas fa-plus"></i> Attend';
                button.onclick = () => toggleAttendance(eventId);
            }
            
            // Update attendees count
            const attendeesElement = document.querySelector(`[data-event-id="${eventId}"] .event-detail i.fa-users`).parentElement;
            const currentCount = parseInt(attendeesElement.textContent.match(/\d+/)[0]);
            const newCount = data.attending ? currentCount + 1 : currentCount - 1;
            attendeesElement.innerHTML = `<i class="fas fa-users"></i><span>${newCount} ${newCount === 1 ? 'person' : 'people'} ${data.attending ? 'going' : 'going'}</span>`;
            
            showNotification(data.attending ? 'You are now attending this event!' : 'You are no longer attending this event.', 'success');
        } else {
            showNotification(data.message || 'Failed to update attendance.', 'error');
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating attendance.', 'error');
        button.innerHTML = originalText;
    })
    .finally(() => {
        button.disabled = false;
    });
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .notification-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    `;
    document.head.appendChild(style);
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('createEventModal');
    if (e.target === modal) {
        closeCreateEventModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateEventModal();
    }
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Events page loaded');
    
    // Add hover effects to event cards
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
