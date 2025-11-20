@extends('layouts.app')

@section('title')
    <title>Sales & Promotion Dashboard</title>
@endsection

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/prism.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
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
        .select2-container .select2-selection--single{
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
                        <li class="breadcrumb-item active" aria-current="page">Sales & Promotion Dashboard</li>
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
                                <label for="filter-region" class="form-label">Region</label>
                                <select id="filter-region" name="region" class="form-select select2">
                                    <option value="">All Regions</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filter-category" class="form-label">Category</label>
                                <select id="filter-category" name="category" class="form-select select2">
                                    <option value="">All Categories</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filter-date-range" class="form-label">Date Range</label>
                               <div class="input-group">
                                    <input type="text" id="filter-date-range" class="form-control" placeholder="Select date (optional)">
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


        <!-- Sales Overview Stats -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">Sales Overview</h4>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <span class="d-block text-muted mb-1 small">Total Revenue</span>
                        <h5 id="stat-total-revenue" class="mb-0">Loading...</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <span class="d-block text-muted mb-1 small">Total Gross Profit</span>
                        <h5 id="stat-total-gross-profit" class="mb-0">Loading...</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <span class="d-block text-muted mb-1 small">Total Transactions</span>
                        <h5 id="stat-total-transactions" class="mb-0">Loading...</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <span class="d-block text-muted mb-1 small">Items Sold</span>
                        <h5 id="stat-total-quantity-sold" class="mb-0">Loading...</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <span class="d-block text-muted mb-1 small">Total Cost</span>
                        <h5 id="stat-total-cost" class="mb-0">Loading...</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <span class="d-block text-muted mb-1 small">Avg. Profit</span>
                        <h5 id="stat-avg-profit" class="mb-0">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Performance Charts -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">Sales Performance</h4>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Monthly Gross Profit Trend</h5>
                        <div class="chart-container">
                            <canvas id="grossProfitChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Top 5 Products by Gross Profit</h5>
                        <div class="chart-container">
                            <canvas id="top5ProductsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Multi-Dimensional Analysis -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Monthly Gross Profit Trend by Product Category</h5>
                        <div class="chart-container" style="height: 400px;">
                            <canvas id="profitByCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promotion Analysis -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">Promotion Analysis</h4>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Top 10 Unsold Promotional Products by Region</h5>
                        <div class="chart-container" style="height: 400px;">
                            <canvas id="unsoldProductsChart"></canvas>
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
        let profitByCategoryChart;
        let grossProfitChart, top5ProductsChart, successfulPromoChart, ineffectivePromosChart;
        let unsoldProductsChart;

        // Helper to format currency
        const idrFormatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            notation: 'compact'
        });

        // Helper to format simple numbers
        const numberFormatter = new Intl.NumberFormat('en-US',{
            notation: 'compact',
            maximumFractionDigits: 1
        });

        /**
         * Get current filter values as a URLSearchParams string
         */
        function getFilterParams() {
            const region = $('#filter-region').val();
            const category = $('#filter-category').val();
            const dateRange = $('#filter-date-range').val();
            return new URLSearchParams({
                region: region,
                category: category,
                date_range: dateRange,
            });
        }

        /**
         * Load all dashboard data based on current filters
         */
        function loadAllDashboardData() {
            const params = getFilterParams();

            loadOverviewStats(params);
            loadGrossProfitTrend(params);
            loadTop5ProductsChart(params);
            loadProfitTrendByCategory(params);
            
            loadUnsoldProductsChart(params);
        }

        /**
         * Load the filter dropdowns on page load
         */
        async function loadFilters() {
            try {
                const response = await fetch('{{ route('sales.getFilterOptions') }}');
                const json = await response.json();
                if (json.success) {
                    // Populate regions
                    const regionSelect = $('#filter-region');
                    json.data.regions.forEach(region => {
                        regionSelect.append(new Option(region, region));
                    });

                    // Populate categories
                    const categorySelect = $('#filter-category');
                    json.data.categories.forEach(category => {
                        categorySelect.append(new Option(category, category));
                    });

                    // Populate promotions
                    const promoSelect = $('#filter-promotion');
                    json.data.promotions.forEach(promo => {
                        promoSelect.append(new Option(promo.promotion_name, promo.promotion_id));
                    });
                }
            } catch (error) {
                console.error('Error loading filters:', error);
            }
        }

        /**
         * Load the 6 main KPI cards
         */
        async function loadOverviewStats(params) {
            try {
                const response = await fetch('{{ route('sales.getSalesOverview') }}?' + params);
                const json = await response.json();
                if (json.success && json.data) {
                    const data = json.data;
                    $('#stat-total-revenue').text(idrFormatter.format(data.total_revenue || 0));
                    $('#stat-total-gross-profit').text(idrFormatter.format(data.total_gross_profit || 0));
                    $('#stat-total-transactions').text(numberFormatter.format(data.total_transactions || 0));
                    $('#stat-total-quantity-sold').text(numberFormatter.format(data.total_quantity_sold || 0));
                    $('#stat-total-cost').text(idrFormatter.format(data.total_cost || 0));
                    $('#stat-avg-profit').text(idrFormatter.format(data.avg_gross_profit_per_transaction || 0));
                }
            } catch (error) {
                console.error('Error loading overview stats:', error);
            }
        }

        /**
         * Load the Monthly Gross Profit line chart
         */
        async function loadGrossProfitTrend(params) {
            try {
                const response = await fetch('{{ route('sales.getMonthlyGrossProfitTrend') }}?' + params);
                const json = await response.json();
                if (json.success) {
                    const data = json.data;
                    const labels = data.map(d => `${d.month_name} ${d.year}`);
                    const values = data.map(d => d.total_gross_profit);

                    const ctx = document.getElementById('grossProfitChart').getContext('2d');
                    if (grossProfitChart) {
                        grossProfitChart.destroy();
                    }
                    grossProfitChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Gross Profit',
                                data: values,
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
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading gross profit trend:', error);
            }
        }

        /**
         * Load the Top 5 Products bar chart
         */
        async function loadTop5ProductsChart(params) {
            try {
                const response = await fetch('{{ route('sales.getTop5ProductsByGrossProfit') }}?' + params);
                const json = await response.json();
                if (json.success) {
                    const data = json.data;
                    const labels = data.map(d => d.product_description);
                    const values = data.map(d => d.total_gross_profit);

                    const ctx = document.getElementById('top5ProductsChart').getContext('2d');
                    if (top5ProductsChart) {
                        top5ProductsChart.destroy();
                    }
                    top5ProductsChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Gross Profit',
                                data: values,
                                backgroundColor: 'rgb(16, 185, 129)',
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading top 5 products:', error);
            }
        }

        async function loadProfitTrendByCategory(params) {
            try {
                const response = await fetch('{{ route('sales.getProfitTrendByCategory') }}?' + params);
                const json = await response.json();
                if (!json.success) return;

                const { labels, datasets } = pivotDataForChart(json.data);

                const ctx = document.getElementById('profitByCategoryChart').getContext('2d');
                if (profitByCategoryChart) {
                    profitByCategoryChart.destroy();
                }
                profitByCategoryChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('id-ID', { 
                                            style: 'currency', 
                                            currency: 'IDR', 
                                            notation: 'compact'
                                        }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });

            } catch (error) {
                console.error('Error loading profit by category trend:', error);
            }
        }

        /**
         * Pivot Chart
         */
        function pivotDataForChart(data) {
            const labelsSet = new Set();
            const categories = new Set();
            data.forEach(row => {
                labelsSet.add(`${row.month_name} ${row.year}`);
                categories.add(row.category);
            });

            const sortedLabels = Array.from(labelsSet);
            const labelIndexMap = new Map(sortedLabels.map((label, index) => [label, index]));
            
            const datasetsMap = new Map();
            const colors = ['rgb(59, 130, 246)', 'rgb(16, 185, 129)', 'rgb(239, 68, 68)', 'rgb(249, 115, 22)', 'rgb(139, 92, 246)'];
            let colorIndex = 0;

            categories.forEach(category => {
                datasetsMap.set(category, {
                    label: category,
                    data: Array(sortedLabels.length).fill(0),
                    borderColor: colors[colorIndex % colors.length],
                    tension: 0.4,
                    fill: false
                });
                colorIndex++;
            });

            data.forEach(row => {
                const label = `${row.month_name} ${row.year}`;
                const index = labelIndexMap.get(label);
                if (index !== undefined) {
                    datasetsMap.get(row.category).data[index] = row.total_gross_profit;
                }
            });

            return {
                labels: sortedLabels,
                datasets: Array.from(datasetsMap.values())
            };
        }

        async function loadUnsoldProductsChart(params) {
            try {
                const response = await fetch('{{ route('sales.getUnsoldProductsByRegion') }}?' + params);
                const json = await response.json();
                if (!json.success) return;

                const { labels, datasets } = pivotDataForStackedBar(json.data);

                const ctx = document.getElementById('unsoldProductsChart').getContext('2d');
                if (unsoldProductsChart) {
                    unsoldProductsChart.destroy();
                }
                unsoldProductsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },
                        scales: {
                            x: {
                                stacked: true,
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count of Failed Promotions'
                                }
                            }
                        }
                    }
                });

            } catch (error) {
                console.error('Error loading unsold products by region chart:', error);
            }
        }

        /**
         * Pivots flat SQL data for a Stacked Bar Chart.
         */
        function pivotDataForStackedBar(data) {
            const regionSet = new Set();
            const productFailuresMap = new Map();
            
            data.forEach(row => {
                regionSet.add(row.region);
                if (!productFailuresMap.has(row.product_description)) {
                    productFailuresMap.set(row.product_description, row.total_failures);
                }
            });

            const sortedLabels = Array.from(productFailuresMap.entries())
                .sort((a, b) => b[1] - a[1])
                .map(entry => entry[0]);

            const labelIndexMap = new Map(sortedLabels.map((label, index) => [label, index]));
            const datasetsMap = new Map();
            const sortedRegions = Array.from(regionSet);
            const colors = ['rgb(239, 68, 68)', 'rgb(249, 115, 22)', 'rgb(245, 158, 11)', 'rgb(132, 204, 22)'];
            let colorIndex = 0;

            sortedRegions.forEach(region => {
                datasetsMap.set(region, {
                    label: region,
                    data: Array(sortedLabels.length).fill(0),
                    backgroundColor: colors[colorIndex % colors.length],
                });
                colorIndex++;
            });

            data.forEach(row => {
                const index = labelIndexMap.get(row.product_description);
                if (index !== undefined) {
                    datasetsMap.get(row.region).data[index] = row.unsold_count_by_region;
                }
            });

            return {
                labels: sortedLabels,
                datasets: Array.from(datasetsMap.values())
            };
        }

        // Document ready
        $(document).ready(function() {
            // Load filters on page load
            loadFilters();

            $('.select2').select2({
                width: '100%',
                allowClear: true
            });

            flatpickr('#filter-date-range', {
                mode: 'range',
                dateFormat: 'Y-m-d'
            });
            
            // Load all dashboard data
            loadAllDashboardData();

            // Apply filters button
            $('#apply-filters').on('click', function() {
                loadAllDashboardData();
            });
        });
    </script>
@endsection