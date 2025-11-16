<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lead Time Chart - Procurement</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            color: #333;
        }
        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .controls label {
            font-weight: bold;
            color: #555;
        }
        .controls input, .controls select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .controls button {
            padding: 8px 16px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .controls button:hover {
            background: #0b5ed7;
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-top: 30px;
        }
        .info {
            margin-top: 20px;
            padding: 15px;
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            border-radius: 4px;
            font-size: 14px;
            color: #004085;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0d6efd;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/procurement" class="back-link">← Back to Procurement</a>

        <h1>Average Lead Time by Vendor</h1>
        <p>Order to Receipt duration (in days) grouped by month</p>

        <div class="controls">
            <label for="months">Show last</label>
            <select id="months">
                <option value="3">3 months</option>
                <option value="6">6 months</option>
                <option value="12" selected>12 months</option>
                <option value="24">24 months</option>
            </select>
            <button onclick="loadChart()">Refresh</button>
        </div>

        <div class="loading" id="loading">Loading chart data...</div>
        <div class="chart-container" id="chartContainer" style="display: none;">
            <canvas id="leadTimeChart"></canvas>
        </div>

        <div class="info">
            <strong>Information:</strong> Chart shows the average number of days from Purchase Order creation until warehouse receipt for each vendor, aggregated by month.
        </div>
    </div>

    <script>
    let chartInstance = null;
    const chartColors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
    ];

    function loadChart() {
        const months = document.getElementById('months').value;
        const loading = document.getElementById('loading');
        const container = document.getElementById('chartContainer');

        loading.style.display = 'block';
        container.style.display = 'none';

        fetch(`/procurement/chart-lead-time/data?months=${months}`)
            .then(response => response.json())
            .then(data => {
                renderChart(data);
                loading.style.display = 'none';
                container.style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading chart data:', error);
                loading.innerHTML = '<p style="color: red;">Error loading chart data</p>';
            });
    }

    function renderChart(data) {
        const ctx = document.getElementById('leadTimeChart').getContext('2d');
        const monthLabels = data.months || [];
        const vendorDatasets = [];

        // Create a dataset for each vendor
        Object.entries(data.vendors || {}).forEach((entry, idx) => {
            const vendorName = entry[0];
            const values = entry[1];
            const color = chartColors[idx % chartColors.length];

            vendorDatasets.push({
                label: vendorName,
                data: values,
                borderColor: color,
                backgroundColor: color + '20', // semi-transparent
                borderWidth: 2,
                fill: false,
                tension: 0.4,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            });
        });

        // Destroy old chart if exists
        if (chartInstance) {
            chartInstance.destroy();
        }

        // Create new chart
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: vendorDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 13 }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 13 },
                        bodyFont: { size: 12 },
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(2) + ' days';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Month',
                            font: { size: 13, weight: 'bold' }
                        },
                        grid: { display: false }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Average Lead Time (days)',
                            font: { size: 13, weight: 'bold' }
                        },
                        beginAtZero: true,
                        grid: { color: '#e0e0e0' }
                    }
                }
            }
        });
    }

    // Load chart on page load
    document.addEventListener('DOMContentLoaded', loadChart);
    </script>
</body>
</html>
