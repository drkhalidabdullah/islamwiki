function editRole(roleId) {
    // Get role data from the role card
    const roleCard = document.querySelector(`[data-role-id="${roleId}"]`);
    const permissionsJson = roleCard.getAttribute('data-permissions');
    
    // Set the role ID
    document.getElementById('edit_role_id').value = roleId;
    
    // Clear all checkboxes first
    const checkboxes = document.querySelectorAll('.edit-permission');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Parse the permissions and check the appropriate checkboxes
    if (permissionsJson) {
        try {
            const permissions = JSON.parse(permissionsJson);
            permissions.forEach(permission => {
                const checkbox = document.querySelector(`input[data-perm="${permission}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        } catch (e) {
            console.error('Error parsing permissions:', e);
        }
    }
    
    // Show the modal
    document.getElementById('editRoleModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editRoleModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editRoleModal');
    if (event.target === modal) {
        closeModal();
    }
}
