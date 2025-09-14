function editCategory(categoryId) {
    // Get category data (you would typically fetch this via AJAX)
    // For now, we'll use a simple approach
    const modal = document.getElementById('editModal');
    document.getElementById('edit_category_id').value = categoryId;
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeModal();
    }
}
