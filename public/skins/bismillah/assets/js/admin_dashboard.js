function toggleMaintenanceMode() {
    if (confirm('Are you sure you want to toggle maintenance mode? This will affect all users.')) {
        // Create a form to submit the toggle request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'toggle_maintenance';
        input.value = '1';
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
