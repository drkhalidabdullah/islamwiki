function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_first_name').value = user.first_name;
    document.getElementById('edit_last_name').value = user.last_name;
    document.getElementById('edit_display_name').value = user.display_name || '';
    document.getElementById('edit_is_active').checked = user.is_active == 1;
    
    document.getElementById('editUserModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
}

function resetPassword(userId, username) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('reset_username').textContent = username;
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
    
    document.getElementById('resetPasswordModal').style.display = 'block';
}

function closeResetModal() {
    document.getElementById('resetPasswordModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editUserModal');
    const resetModal = document.getElementById('resetPasswordModal');
    
    if (event.target == editModal) {
        closeEditModal();
    }
    if (event.target == resetModal) {
        closeResetModal();
    }
}

// Password confirmation validation
document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
});
