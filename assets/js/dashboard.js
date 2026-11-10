

document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartTahunMasuk');
    if (!ctx) return;

    const chartData = window.dashboardChartData || {};
    const labels = chartData.labels || [];
    const dataTotal = chartData.total || [];
    const dataDiterima = chartData.diterima || [];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Terdaftar',
                    data: dataTotal,
                    borderColor: '#1d4ed8', 
                    backgroundColor: 'rgba(29, 78, 216, 0.08)',
                    tension: 0.45,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#1d4ed8',
                    fill: false
                },
                {
                    label: 'Diterima',
                    data: dataDiterima,
                    borderColor: '#16a34a', 
                    backgroundColor: 'rgba(22, 163, 74, 0.08)',
                    tension: 0.45,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#16a34a',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    display: false 
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#f9fafb',
                    bodyColor: '#f9fafb',
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(229, 231, 235, 0.6)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6b7280',
                        font: { size: 12, weight: '500' }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#6b7280',
                        font: { size: 12, weight: '500' }
                    },
                    grid: {
                        color: 'rgba(229, 231, 235, 0.6)',
                        drawBorder: false
                    }
                }
            }
        }
    });
});
