function clearFilters() {
    document.getElementById('namespace').value = 'all';
    document.getElementById('type').value = 'all';
    document.getElementById('category').value = '';
    document.getElementById('author').value = '';
    document.getElementById('date_from').value = '';
    document.getElementById('date_to').value = '';
    document.getElementById('sort').value = 'relevance';
}
