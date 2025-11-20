@extends('layouts.app')

@section('title')
    <title>Inventory Accumulating Snapshot - Average Lead Time</title>
@endsection

@section('styles')
    <style>
        body {
            background-color: #f5f7fa;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }

        .card-title {
            margin-bottom: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0.5rem 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item {
            font-size: 0.875rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        .controls {
            display: flex;
            gap: 15px;
            margin-bottom: 0;
            align-items: center;
            flex-wrap: wrap;
        }

        .controls label {
            font-weight: 600;
            color: #333;
            margin: 0;
            font-size: 0.95rem;
        }

        .controls select {
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            cursor: pointer;
            background-color: white;
            transition: border-color 0.2s;
        }

        .controls select:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .controls button {
            padding: 10px 25px;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .controls button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(5, 150, 105, 0.4);
        }

        .chart-wrapper {
            position: relative;
            height: 600px;
            margin: 0;
            display: none;
            padding: 20px 0;
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
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 0.5rem;
            margin: 0;
            display: none;
        }

        .error.show {
            display: block;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 20px;
            border-radius: 0.5rem;
            margin-top: 0;
            color: #333;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .info-box strong {
            color: #0d6efd;
        }

        .vendor-filters {
            background: white;
            border: none;
            padding: 0;
            border-radius: 0;
            margin: 0;
            max-height: none;
            overflow-y: visible;
            box-shadow: none;
        }

        .vendor-filters h3 {
            display: none;
        }

        .vendor-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
        }

        .vendor-checkbox {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .vendor-checkbox input {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: #10b981;
            transition: all 0.3s ease;
        }

        .vendor-checkbox input:hover {
            transform: scale(1.1);
        }

        .vendor-checkbox input:checked {
            accent-color: #059669;
        }

        .vendor-checkbox label {
            cursor: pointer;
            font-size: 0.9rem;
            color: #333;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 0.4rem;
            background: rgba(16, 185, 129, 0.05);
            transition: all 0.3s ease;
            flex: 1;
            margin: 0;
        }

        .vendor-checkbox input:checked + label {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-weight: 600;
            padding: 8px 12px;
        }

        .vendor-checkbox:hover label {
            background: rgba(16, 185, 129, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .filter-buttons button {
            padding: 10px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            background: white;
            border: 2px solid #10b981;
            color: #10b981;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-buttons button:hover {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .filter-buttons button:active {
            transform: translateY(0);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Inventory Accumulating Snapshot</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="page-title">📊 Average Lead Time by Vendor - Monthly Analysis</h1>
                <p class="page-subtitle">Average duration (days) from Purchase Order creation to warehouse receipt, grouped by vendor and month</p>
            </div>
        </div>

        <!-- Controls Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Chart Controls</h5>
                    </div>
                    <div class="card-body">
                        <div class="controls">
                            <label for="monthSelect">Display Period:</label>
                            <select id="monthSelect">
                                <option value="3">Last 3 months</option>
                                <option value="6">Last 6 months</option>
                                <option value="12" selected>Last 12 months</option>
                                <option value="24">Last 24 months</option>
                            </select>
                            <button onclick="loadChart()">Load Chart</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Filters Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Vendor Filters</h5>
                    </div>
                    <div class="card-body">
                        <div class="vendor-filters" id="vendorFilters">
                            <div class="filter-buttons">
                                <button onclick="selectAllVendors()">Select All</button>
                                <button onclick="clearAllVendors()">Clear All</button>
                            </div>
                            <div class="vendor-list" id="vendorList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Lead Time Trend</h5>
                    </div>
                    <div class="card-body">
                        <div class="error" id="errorBox"></div>
                        <div class="loading" id="loading">Loading chart data</div>
                        <div class="chart-wrapper" id="chartWrapper">
                            <canvas id="leadTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="row">
            <div class="col-12">
                <div class="info-box">
                    <strong>Information:</strong> This chart displays the average duration from Purchase Order creation to goods receipt at the warehouse, grouped by vendor and month.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
    let chartInstance = null;
    let chartData = null;
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

    function buildVendorFilters(vendors) {
        const filterContainer = document.getElementById('vendorFilters');
        const vendorList = document.getElementById('vendorList');
        vendorList.innerHTML = '';

        vendors.forEach(vendor => {
            const checkbox = document.createElement('div');
            checkbox.className = 'vendor-checkbox';
            checkbox.innerHTML = `
                <input type="checkbox" id="vendor-${vendor}" value="${vendor}" checked onchange="updateChartDisplay()">
                <label for="vendor-${vendor}">${vendor}</label>
            `;
            vendorList.appendChild(checkbox);
        });

        filterContainer.style.display = 'block';
    }

    function getSelectedVendors() {
        const checkboxes = document.querySelectorAll('#vendorList input[type="checkbox"]');
        return Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
    }

    function selectAllVendors() {
        document.querySelectorAll('#vendorList input[type="checkbox"]').forEach(cb => {
            cb.checked = true;
        });
        updateChartDisplay();
    }

    function clearAllVendors() {
        document.querySelectorAll('#vendorList input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        updateChartDisplay();
    }

    function updateChartDisplay() {
        if (!chartData) return;
        
        const selectedVendors = getSelectedVendors();
        const datasets = [];
        let minValue = Infinity;
        let maxValue = 0;

        // Collect all selected vendor data to find min/max
        chartData.vendors.forEach((vendor, idx) => {
            if (selectedVendors.includes(vendor.name)) {
                vendor.data.forEach(value => {
                    if (value !== null && value !== undefined) {
                        minValue = Math.min(minValue, value);
                        maxValue = Math.max(maxValue, value);
                    }
                });
            }
        });

        // Calculate min scale (1 step dibawah min value)
        const stepSize = 0.1;
        const minScale = Math.floor((minValue - stepSize) * 10) / 10;
        const maxScale = Math.ceil((maxValue + stepSize) * 10) / 10;

        chartData.vendors.forEach((vendor, idx) => {
            if (selectedVendors.includes(vendor.name)) {
                const color = colors[idx % colors.length];
                datasets.push({
                    label: vendor.name,
                    data: vendor.data,
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
            }
        });

        if (chartInstance) chartInstance.destroy();

        const ctx = document.getElementById('leadTimeChart').getContext('2d');
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.months,
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
                        min: minScale,
                        max: maxScale,
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: true },
                        ticks: { font: { size: 11 }, stepSize: 0.1 },
                        padding: { top: 30, bottom: 30 }
                    }
                }
            }
        });
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
                    // Transform data format
                    const vendorArray = Object.entries(result.vendors).map((entry, idx) => ({
                        name: entry[0],
                        data: entry[1]
                    }));
                    
                    chartData = {
                        months: result.months,
                        vendors: vendorArray
                    };

                    buildVendorFilters(vendorArray.map(v => v.name));
                    updateChartDisplay();
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

    // Auto-load chart on page load
    window.addEventListener('load', loadChart);
    </script>
@endsection
