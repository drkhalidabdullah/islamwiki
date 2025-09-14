// Chart data
const pageViewsData = 
const userActionsData = 

// Page Views Chart
const pageViewsCtx = document.getElementById('pageViewsChart').getContext('2d');
new Chart(pageViewsCtx, {
    type: 'line',
    data: {
        labels: pageViewsData.map(item => item.date),
        datasets: [{
            label: 'Page Views',
            data: pageViewsData.map(item => item.count),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// User Actions Chart
const userActionsCtx = document.getElementById('userActionsChart').getContext('2d');
new Chart(userActionsCtx, {
    type: 'bar',
    data: {
        labels: userActionsData.map(item => item.date),
        datasets: [{
            label: 'User Actions',
            data: userActionsData.map(item => item.count),
            backgroundColor: '#28a745',
            borderColor: '#1e7e34',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Update analytics when time period changes
function updateAnalytics() {
    const days = document.getElementById('days').value;
    window.location.href = `?days=${days}`;
}
// Chart data
const pageViewsData = 
const userActionsData = 

// Page Views Chart
const pageViewsCtx = document.getElementById('pageViewsChart').getContext('2d');
new Chart(pageViewsCtx, {
    type: 'line',
    data: {
        labels: pageViewsData.map(item => item.date),
        datasets: [{
            label: 'Page Views',
            data: pageViewsData.map(item => item.count),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// User Actions Chart
const userActionsCtx = document.getElementById('userActionsChart').getContext('2d');
new Chart(userActionsCtx, {
    type: 'bar',
    data: {
        labels: userActionsData.map(item => item.date),
        datasets: [{
            label: 'User Actions',
            data: userActionsData.map(item => item.count),
            backgroundColor: '#28a745',
            borderColor: '#1e7e34',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Update analytics when time period changes
function updateAnalytics() {
    const days = document.getElementById('days').value;
    window.location.href = `?days=${days}`;
}
