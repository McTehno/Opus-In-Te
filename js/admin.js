document.addEventListener('DOMContentLoaded', function() {
    // Animate Numbers
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(num => {
        const target = +num.getAttribute('data-target');
        const duration = 1500; // ms
        const increment = target / (duration / 16); // 60fps
        
        let current = 0;
        const updateCount = () => {
            current += increment;
            if (current < target) {
                num.innerText = Math.ceil(current);
                requestAnimationFrame(updateCount);
            } else {
                num.innerText = target;
            }
        };
        updateCount();
    });

    // Initialize Charts
    initCharts();

    // Expand List Functionality
    const expandBtn = document.getElementById('expand-appointments');
    const hiddenRows = document.querySelectorAll('.appointment-item.hidden');
    
    if(expandBtn) {
        expandBtn.addEventListener('click', function() {
            hiddenRows.forEach(row => {
                row.classList.remove('hidden');
                row.style.display = 'flex'; // Restore display
                row.style.animation = 'fadeInUp 0.5s forwards';
            });
            this.style.display = 'none'; // Hide button after expanding
        });
    }
});

function initCharts() {
    // Data is injected from PHP into global variables: adminChartData
    if (typeof adminChartData === 'undefined') return;

    const dataPoints = adminChartData.dates.length;
    const minWidthPerPoint = 60; // px per day
    const totalWidth = Math.max(dataPoints * minWidthPerPoint, 600); // Min 600px

    // Appointments Graph (Line)
    const ctxAppt = document.getElementById('appointmentsChart').getContext('2d');
    
    // Set width dynamically for scrolling
    ctxAppt.canvas.parentNode.style.width = `${totalWidth}px`;

    new Chart(ctxAppt, {
        type: 'line',
        data: {
            labels: adminChartData.dates,
            datasets: [{
                label: 'Broj Termina',
                data: adminChartData.appointmentCounts,
                borderColor: '#C5A76A',
                backgroundColor: 'rgba(197, 167, 106, 0.1)',
                borderWidth: 2,
                tension: 0.4, // Smooth curves
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#C5A76A'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                },
                x: {
                    grid: { display: false }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });

    // Income Graph (Column/Bar)
    const ctxIncome = document.getElementById('incomeChart').getContext('2d');
    
    // Set width dynamically for scrolling
    ctxIncome.canvas.parentNode.style.width = `${totalWidth}px`;

    new Chart(ctxIncome, {
        type: 'bar',
        data: {
            labels: adminChartData.dates,
            datasets: [{
                label: 'Dnevni Prihod (KM)',
                data: adminChartData.dailyIncome,
                backgroundColor: '#C5A76A',
                borderRadius: 4,
                barThickness: 'flex',
                maxBarThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return value + ' KM'; }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}
