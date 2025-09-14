function showDeactivateModal() {
    document.getElementById('deactivateModal').style.display = 'block';
}

function hideDeactivateModal() {
    document.getElementById('deactivateModal').style.display = 'none';
}

function showDeleteModal() {
    document.getElementById('deleteModal').style.display = 'block';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const deactivateModal = document.getElementById('deactivateModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === deactivateModal) {
        hideDeactivateModal();
    }
    if (event.target === deleteModal) {
        hideDeleteModal();
    }
}
