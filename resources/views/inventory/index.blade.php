@extends('layouts.app')

@section('title')
    <title>Inventory Dashboard</title>
@endsection

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/prism.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">

    <style>
        .stat-card {
            transition: transform 0.2s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .select2-container .select2-selection--single {
            height: 100%;
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
                        <li class="breadcrumb-item active" aria-current="page">Inventory Dashboard</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Filters</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filter-warehouse" class="form-label">Warehouse</label>
                                <select id="filter-warehouse" class="form-select select2">
                                    <option value="">All Warehouses</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filter-category" class="form-label">Category</label>
                                <select id="filter-category" class="form-select select2">
                                    <option value="">All Categories</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filter-date" class="form-label">Date</label>
                                <div class="input-group">
                                    <input type="text" id="filter-date" class="form-control"
                                        placeholder="Select date (optional)">
                                    <button class="btn btn-outline-secondary" type="button" id="clear-date">
                                        <i class="fa fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button id="apply-filters" class="btn btn-primary w-100">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Overview Stats -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">Inventory Overview</h4>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <span class="d-block text-muted mb-1 small">Total Quantity On Hand</span>
                        <h5 id="stat-total-qty" class="mb-0">Loading...</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <span class="d-block text-muted mb-1 small">Total Value On Hand</span>
                        <h5 id="stat-total-value" class="mb-0">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quantity Trend Charts -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">Quantity Trends</h4>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Quantity On Hand by Date</h5>
                        <div class="chart-container">
                            <canvas id="qty-by-date-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Quantity by Warehouse</h5>
                        <div class="chart-container">
                            <canvas id="qty-by-warehouse-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3-dimensional view: Dates x Warehouses (stacked bar) -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Quantity by Date & Warehouse (stacked)</h5>
                        <div class="chart-container" style="height:420px;">
                            <canvas id="qty-date-warehouse-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Value Analysis -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">Value Analysis</h4>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Value On Hand by Warehouse</h5>
                        <div class="chart-container">
                            <canvas id="value-by-warehouse-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Top 10 Products by Quantity</h5>
                        <div class="chart-container">
                            <canvas id="top-products-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warehouse Distribution -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Quantity Distribution by Warehouse & Category</h5>
                        <div class="chart-container" style="height: 400px;">
                            <canvas id="warehouse-category-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js\axios.min.js') }}"></script>
    <script src="{{ asset('js\vue.js') }}"></script>

    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('js/fileinput/fileinput.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/themes/fa5/theme.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Chart variables
        let qtyByDateChart, qtyByWarehouseChart, qtyDateWarehouseChart, valueByWarehouseChart, topProductsChart, warehouseCategoryChart;

        // Helper to format currency
        const idrFormatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        });

        // Helper to format simple numbers
        const numberFormatter = new Intl.NumberFormat('id-ID');

        function formatLargeNumber(value, isCurrency = false) {
            const absValue = Math.abs(value);
            let formatted = '';

            if (absValue >= 1000000000) {
                // Miliar
                formatted = (value / 1000000000).toFixed(2) + 'B'; // M = Miliar
            } else if (absValue >= 1000000) {
                // Juta
                formatted = (value / 1000000).toFixed(2) + 'M';
            } else if (absValue >= 1000) {
                // Ribu
                formatted = (value / 1000).toFixed(2) + 'K';
            } else {
                formatted = value.toFixed(0);
            }

            return isCurrency ? 'Rp ' + formatted : formatted;
        }


        /**
         * Get current filter values
         */
        function getFilterParams() {
            const warehouse = $('#filter-warehouse').val();
            const category = $('#filter-category').val();
            const date = $('#filter-date').val();
            return new URLSearchParams({
                warehouse_id: warehouse,
                category: category,
                date: date
            });
        }

        /**
         * Load all inventory dashboard data
         */
        function loadAllInventoryData() {
            const params = getFilterParams();

            loadInventoryOverview(params);
            loadQtyByDateChart(params);
            loadQtyByWarehouseChart(params);
            loadQtyDateWarehouseChart(params); // new stacked chart
            loadValueByWarehouseChart(params);
            loadTopProductsChart(params);
            loadWarehouseCategoryChart(params);
        }

        /**
         * Load filter options
         */
        async function loadFilters() {
            try {
                const response = await fetch('{{ route('inventory.getFilterOptions') }}');
                const json = await response.json();
                if (json.success) {
                    // Populate warehouses
                    if (json.data.warehouses) {
                        const warehouseSelect = $('#filter-warehouse');
                        json.data.warehouses.forEach(warehouse => {
                            warehouseSelect.append(
                                `<option value="${warehouse.warehouse_id}">${warehouse.warehouse_name}</option>`
                            );
                        });
                    }

                    // Populate categories
                    if (json.data.categories) {
                        const categorySelect = $('#filter-category');
                        json.data.categories.forEach(cat => {
                            categorySelect.append(
                                `<option value="${cat}">${cat}</option>`
                            );
                        });
                    }

                    // Load dashboard data AFTER filters are loaded
                    loadAllInventoryData();
                }
            } catch (error) {
                console.error('Error loading filters:', error);
            }
        }

        /**
         * Load inventory overview stats
         */
        async function loadInventoryOverview(params) {
            try {
                const response = await fetch('{{ route('inventory.getOverview') }}?' + params);
                const json = await response.json();
                if (json.success && json.data) {
                    const data = json.data;

                    // Format dengan singkatan untuk readability
                    $('#stat-total-qty').text(formatLargeNumber(data.total_qty || 0));
                    $('#stat-total-value').text(formatLargeNumber(data.total_value || 0, true));
                }
            } catch (error) {
                console.error('Error loading overview:', error);
            }
        }


        /**
         * Load Quantity by Date chart
         */
        async function loadQtyByDateChart(params) {
            try {
                const response = await fetch('{{ route('inventory.getQtyByDate') }}?' + params);
                const json = await response.json();
                if (json.success && json.data) {
                    const ctx = document.getElementById('qty-by-date-chart').getContext('2d');
                    if (qtyByDateChart) qtyByDateChart.destroy();

                    qtyByDateChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: json.data.labels,
                            datasets: [{
                                label: 'Quantity On Hand',
                                data: json.data.values,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            return numberFormatter.format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading qty by date chart:', error);
            }
        }

        /**
         * Load Quantity by Warehouse chart
         */
        async function loadQtyByWarehouseChart(params) {
            try {
                const response = await fetch('{{ route('inventory.getQtyByWarehouse') }}?' + params);
                const json = await response.json();
                if (json.success && json.data) {
                    const ctx = document.getElementById('qty-by-warehouse-chart').getContext('2d');
                    if (qtyByWarehouseChart) qtyByWarehouseChart.destroy();

                    qtyByWarehouseChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: json.data.labels,
                            datasets: [{
                                label: 'Quantity On Hand',
                                data: json.data.values,
                                backgroundColor: 'rgb(16, 185, 129)',
                                borderColor: 'rgb(16, 185, 129)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        callback: function (value) {
                                            return numberFormatter.format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading qty by warehouse chart:', error);
            }
        }

        /**
         * Load Quantity by Date & Warehouse stacked chart (3-dim view)
         * Expects backend to return: { labels: [date1,...], warehouses: ['WH1','WH2'...], data: { 'WH1': [v1,v2...], 'WH2': [...] } }
         */
        async function loadQtyDateWarehouseChart(params) {
            try {
                const response = await fetch('{{ route('inventory.getQtyByDateWarehouse') }}?' + params);
                const json = await response.json();
                if (!json.success || !json.data) return;

                const labels = json.data.labels;
                const warehouses = json.data.warehouses; // array of warehouse names or ids
                const raw = json.data.data; // object keyed by warehouse -> array of values aligned to labels

                const datasets = warehouses.map((wh, idx) => {
                    const palette = ['rgb(59, 130, 246)', 'rgb(16, 185, 129)', 'rgb(239, 68, 68)', 'rgb(249, 115, 22)', 'rgb(139, 92, 246)', 'rgb(236, 72, 153)', 'rgb(6, 182, 212)', 'rgb(34, 197, 94)'];
                    return {
                        label: wh,
                        data: raw[wh] || Array(labels.length).fill(0),
                        backgroundColor: palette[idx % palette.length],
                        stack: 'stack1'
                    };
                });

                const ctx = document.getElementById('qty-date-warehouse-chart').getContext('2d');
                if (qtyDateWarehouseChart) qtyDateWarehouseChart.destroy();

                qtyDateWarehouseChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return context.dataset.label + ': ' + numberFormatter.format(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { stacked: true },
                            y: {
                                stacked: true,
                                ticks: {
                                    callback: function (value) { return numberFormatter.format(value); }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error loading qty-date-warehouse chart:', error);
            }
        }

        /**
         * Load Value by Warehouse chart
         */
        async function loadValueByWarehouseChart(params) {
            try {
                const response = await fetch('{{ route('inventory.getValueByWarehouse') }}?' + params);
                const json = await response.json();
                if (json.success && json.data) {
                    const ctx = document.getElementById('value-by-warehouse-chart').getContext('2d');
                    if (valueByWarehouseChart) valueByWarehouseChart.destroy();

                    // Calculate total for percentage
                    const total = json.data.values.reduce((a, b) => a + b, 0);

                    // Create labels with values and percentage
                    const labelsWithValues = json.data.labels.map((label, index) => {
                        const value = json.data.values[index];
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + idrFormatter.format(value) + ' (' + percentage + '%)';
                    });

                    valueByWarehouseChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labelsWithValues,
                            datasets: [{
                                data: json.data.values,
                                backgroundColor: [
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(239, 68, 68)',
                                    'rgb(249, 115, 22)',
                                    'rgb(139, 92, 246)',
                                    'rgb(236, 72, 153)',
                                    'rgb(6, 182, 212)',
                                    'rgb(34, 197, 94)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 10,
                                        font: {
                                            size: 11
                                        },
                                        boxWidth: 15
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const label = json.data.labels[context.dataIndex] || '';
                                            const value = idrFormatter.format(context.parsed);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading value by warehouse chart:', error);
            }
        }



        /**
         * Load Top Products chart
         */
        async function loadTopProductsChart(params) {
            try {
                const response = await fetch('{{ route('inventory.getTopProducts') }}?' + params);
                const json = await response.json();
                if (json.success && json.data) {
                    const ctx = document.getElementById('top-products-chart').getContext('2d');
                    if (topProductsChart) topProductsChart.destroy();

                    topProductsChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: json.data.labels,
                            datasets: [{
                                label: 'Quantity',
                                data: json.data.values,
                                backgroundColor: 'rgb(249, 115, 22)',
                                borderColor: 'rgb(249, 115, 22)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        callback: function (value) { return numberFormatter.format(value); }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading top products chart:', error);
            }
        }

        /**
         * Load Warehouse & Category distribution chart
         */
        async function loadWarehouseCategoryChart(params) {
            try {
                const response = await fetch('{{ route('inventory.getWarehouseCategoryData') }}?' + params);
                const json = await response.json();
                if (json.success && json.data) {
                    const ctx = document.getElementById('warehouse-category-chart').getContext('2d');
                    if (warehouseCategoryChart) warehouseCategoryChart.destroy();

                    warehouseCategoryChart = new Chart(ctx, {
                        type: 'bar',
                        data: json.data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        boxWidth: 12,
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return context.dataset.label + ': ' + numberFormatter.format(context.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    // Grouped bar - tidak perlu stacked
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    // Grouped bar - tidak perlu stacked
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            return numberFormatter.format(value);
                                        }
                                    }
                                }
                            },
                            // Optional: Atur bar thickness
                            barPercentage: 0.9,
                            categoryPercentage: 0.8
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading warehouse category chart:', error);
            }
        }


        // Document ready
        $(document).ready(function () {
            // Load filters
            loadFilters();

            $('.select2').select2({
                width: '100%',
                allowClear: true
            });

            // Initialize date range picker
            flatpickr('#filter-date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
            });

            // Load dashboard data
            // loadAllInventoryData();

            // Apply filters button
            $('#apply-filters').on('click', function () {
                loadAllInventoryData();
            });

            $('#clear-date').on('click', function () {
                $('#filter-date').val('');
                // Optional: auto reload data setelah clear
                loadAllInventoryData();
            });
        });
    </script>
@endsection