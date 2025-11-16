<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rata-rata Lead Time Bulanan per Vendor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .controls {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: center;
        }
        .controls label {
            font-weight: 600;
            color: #333;
        }
        .controls select {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .controls select:hover {
            border-color: #667eea;
        }
        .controls button {
            padding: 10px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .controls button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .chart-wrapper {
            position: relative;
            height: 450px;
            margin: 30px 0;
            display: none;
        }
        .chart-wrapper.show {
            display: block;
        }
        .loading {
            text-align: center;
            padding: 60px 20px;
            color: #667eea;
            font-size: 16px;
            font-weight: 600;
        }
        .loading::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }
        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }
        .error {
            background: #fee;
            border: 2px solid #f88;
            color: #c33;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            display: none;
        }
        .error.show {
            display: block;
        }
        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 6px;
            margin-top: 30px;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }
        .info-box strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Rata-rata Lead Time Bulanan per Vendor</h1>
        <p class="subtitle">Durasi rata-rata (hari) dari PO dibuat sampai barang diterima, per vendor, per bulan</p>

        <div class="controls">
            <label for="monthSelect">Tampilkan:</label>
            <select id="monthSelect">
                <option value="3">3 bulan terakhir</option>
                <option value="6">6 bulan terakhir</option>
                <option value="12" selected>12 bulan terakhir</option>
                <option value="24">24 bulan terakhir</option>
            </select>
            <button onclick="loadChart()">Muat Grafik</button>
        </div>

        <div class="error" id="errorBox"></div>
        <div class="loading" id="loading">Memuat data grafik</div>
        <div class="chart-wrapper" id="chartWrapper">
            <canvas id="leadTimeChart"></canvas>
        </div>

        <div class="info-box">
            <strong>Keterangan:</strong> Grafik menampilkan rata-rata durasi dari pembuatan Purchase Order sampai penerimaan barang di warehouse.
        </div>
    </div>

    <script>
    let chartInstance = null;
    const colors = [
        '#667eea', '#764ba2', '#f093fb', '#4facfe',
        '#43e97b', '#fa709a', '#fee140', '#30b0fe',
        '#ec4899', '#8b5cf6', '#06b6d4', '#10b981'
    ];

    function showError(msg) {
        const box = document.getElementById('errorBox');
        box.textContent = msg;
        box.classList.add('show');
    }

    function hideError() {
        document.getElementById('errorBox').classList.remove('show');
    }

    function loadChart() {
        const months = document.getElementById('monthSelect').value;
        const loading = document.getElementById('loading');
        const wrapper = document.getElementById('chartWrapper');

        hideError();
        loading.style.display = 'block';
        wrapper.classList.remove('show');

        fetch(`/procurement/chart-lead-time/data?months=${months}`)
            .then(res => res.json())
            .then(result => {
                loading.style.display = 'none';
                if (result.months && Object.keys(result.vendors).length > 0) {
                    renderChart(result);
                    wrapper.classList.add('show');
                } else {
                    showError('Tidak ada data grafik untuk periode ini');
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                showError('Kesalahan: ' + err.message);
                console.error(err);
            });
    }

    function renderChart(data) {
        const ctx = document.getElementById('leadTimeChart').getContext('2d');
        const datasets = [];

        Object.entries(data.vendors || {}).forEach((entry, idx) => {
            const vendorName = entry[0];
            const values = entry[1];
            const color = colors[idx % colors.length];

            datasets.push({
                label: vendorName,
                data: values,
                borderColor: color,
                backgroundColor: color + '15',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointStyle: 'circle'
            });
        });

        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 12, weight: 'bold' },
                            generateLabels: (chart) => {
                                const datasets = chart.data.datasets;
                                return datasets.map((ds, i) => ({
                                    text: ds.label,
                                    fillStyle: ds.borderColor,
                                    strokeStyle: ds.borderColor,
                                    pointStyle: 'circle',
                                    hidden: false,
                                    index: i
                                }));
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 15,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(2) + ' hari';
                                } else {
                                    label += 'Tidak ada data';
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
                            text: 'Bulan',
                            font: { size: 13, weight: 'bold' },
                            color: '#333'
                        },
                        grid: { display: false, drawBorder: true },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Rata-rata Lead Time (hari)',
                            font: { size: 13, weight: 'bold' },
                            color: '#333'
                        },
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: true },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }

    // Auto-load chart on page load
    window.addEventListener('load', loadChart);
    </script>
</body>
</html>
