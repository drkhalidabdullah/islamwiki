// Analytics data
const analyticsData = 
const trendsData = 
const searchTypesData = 

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initTrendsChart();
    initSearchTypesChart();
});

function initTrendsChart() {
    const ctx = document.getElementById('trendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendsData.labels,
            datasets: [{
                label: 'Searches',
                data: trendsData.searches,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initSearchTypesChart() {
    const ctx = document.getElementById('searchTypesChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(searchTypesData),
            datasets: [{
                data: Object.values(searchTypesData),
                backgroundColor: [
                    '#3498db',
                    '#e74c3c',
                    '#2ecc71',
                    '#f39c12',
                    '#9b59b6'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateAnalytics() {
    const period = document.getElementById('periodSelect').value;
    window.location.href = `/search/analytics?period=${period}`;
}
// Analytics data
const analyticsData = 
const trendsData = 
const searchTypesData = 

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initTrendsChart();
    initSearchTypesChart();
});

function initTrendsChart() {
    const ctx = document.getElementById('trendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendsData.labels,
            datasets: [{
                label: 'Searches',
                data: trendsData.searches,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initSearchTypesChart() {
    const ctx = document.getElementById('searchTypesChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(searchTypesData),
            datasets: [{
                data: Object.values(searchTypesData),
                backgroundColor: [
                    '#3498db',
                    '#e74c3c',
                    '#2ecc71',
                    '#f39c12',
                    '#9b59b6'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateAnalytics() {
    const period = document.getElementById('periodSelect').value;
    window.location.href = `/search/analytics?period=${period}`;
}
