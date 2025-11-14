<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sales Dashboard - {{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        /* Simple dark mode toggle for demo (optional) */
        /* .dark { background-color: #0a0a0a; color: #ededec; } */
        /* You can manage this with JS or a proper Vite setup */
    </style>
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#ededec] font-sans antialiased">

    <div class="container mx-auto max-w-7xl p-4 lg:p-8">

        <header class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-semibold">Sales & Promotion Dashboard</h1>
            <a href="{{ url('/') }}" class="text-sm text-gray-500 hover:underline">Back to Menu</a>
        </header>

        <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="filter-region"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                    <select id="filter-region" name="region"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                        <option value="">All Regions</option>
                    </select>
                </div>
                <div>
                    <label for="filter-category"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select id="filter-category" name="category"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div>
                    <label for="filter-promotion"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Promotion</label>
                    <select id="filter-promotion" name="promotion"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                        <option value="">All Promotions</option>
                    </select>
                </div>
                <div class="self-end">
                    <button id="apply-filters"
                        class="w-full h-10 px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <h2 class="text-2xl font-semibold mb-4">Sales Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</h4>
                <p id="stat-total-revenue" class="text-2xl font-bold">Loading...</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Gross Profit</h4>
                <p id="stat-total-gross-profit" class="text-2xl font-bold">Loading...</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transactions</h4>
                <p id="stat-total-transactions" class="text-2xl font-bold">Loading...</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Items Sold</h4>
                <p id="stat-total-quantity-sold" class="text-2xl font-bold">Loading...</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Cost</h4>
                <p id="stat-total-cost" class="text-2xl font-bold">Loading...</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg. Profit</h4>
                <p id="stat-avg-profit" class="text-2xl font-bold">Loading...</p>
            </div>
        </div>

        <h2 class="text-2xl font-semibold mb-4">Sales Performance</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h3 class="font-semibold mb-2">Monthly Gross Profit Trend</h3>
                <canvas id="grossProfitChart"></canvas>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h3 class="font-semibold mb-2">Top 5 Products by Gross Profit</h3>
                <canvas id="top5ProductsChart"></canvas>
            </div>
        </div>

        <h2 class="text-2xl font-semibold mb-4">Promotion Analysis</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h3 class="font-semibold mb-2">Top 5 Successful Promotions (by Qty Sold)</h3>
                <canvas id="successfulPromoChart"></canvas>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h3 class="font-semibold mb-2">Top 5 Most Ineffective Promotions</h3>
                <canvas id="ineffectivePromosChart"></canvas>
            </div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <h3 class="font-semibold mb-4">List of Unsold Promotional Products</h3>
            <table id="unsoldProductsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="py-3 px-6">Product</th>
                        <th scope="col" class="py-3 px-6">Store</th>
                        <th scope="col" class="py-3 px-6">Promotion</th>
                        <th scope="col" class="py-3 px-6">Start Date</th>
                        <th scope="col" class="py-3 px-6">End Date</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        // UPDATED: Renamed variable for clarity
        let grossProfitChart, top5ProductsChart, successfulPromoChart, ineffectivePromosChart;
        let unsoldTable;

        // Helper to format currency
        const idrFormatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        });

        // Helper to format simple numbers
        const numberFormatter = new Intl.NumberFormat('id-ID');

        /**
         * Get current filter values as a URLSearchParams string
         */
        function getFilterParams() {
            const region = $('#filter-region').val();
            const category = $('#filter-category').val();
            const promotion = $('#filter-promotion').val();
            return new URLSearchParams({
                region: region,
                category: category,
                promotion_id: promotion
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
            
            // UPDATED: Changed function call
            loadSuccessfulPromoChart(params); 
            
            loadIneffectivePromosChart(params);
            loadUnsoldProductsTable(params);
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
                                tension: 0.1
                            }]
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
                            indexAxis: 'y'
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading top 5 products:', error);
            }
        }

        /**
         * UPDATED: Load the Top 5 Successful Promotions bar chart
         */
        async function loadSuccessfulPromoChart(params) {
            try {
                // Fetch from the new route
                const response = await fetch('{{ route('sales.getTop5SuccessfulPromotions') }}?' + params);
                const json = await response.json();
                if (json.success) {
                    const data = json.data;
                    const labels = data.map(d => d.promotion_name);
                    const values = data.map(d => d.total_quantity_sold);

                    // Use the new canvas ID
                    const ctx = document.getElementById('successfulPromoChart').getContext('2d');
                    if (successfulPromoChart) {
                        successfulPromoChart.destroy();
                    }
                    successfulPromoChart = new Chart(ctx, {
                        type: 'bar', // Changed from 'line' to 'bar'
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Quantity Sold',
                                data: values,
                                backgroundColor: 'rgb(34, 197, 94)', // Green color for success
                                tension: 0.1
                            }]
                        },
                        options: {
                            indexAxis: 'y' // Horizontal bar chart
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading successful promo chart:', error);
            }
        }

        /**
         * Load the Top 5 Most Ineffective Promotions bar chart
         */
        async function loadIneffectivePromosChart(params) {
            try {
                const response = await fetch('{{ route('sales.getMostIneffectivePromotions') }}?' + params);
                const json = await response.json();
                if (json.success) {
                    const data = json.data;
                    const labels = data.map(d => d.promotion_name);
                    const values = data.map(d => d.unsold_items_count);

                    const ctx = document.getElementById('ineffectivePromosChart').getContext('2d');
                    if (ineffectivePromosChart) {
                        ineffectivePromosChart.destroy();
                    }
                    ineffectivePromosChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Failed Promo Item Count',
                                data: values,
                                backgroundColor: 'rgb(239, 68, 68)', // Red color for failure
                            }]
                        },
                        options: {
                            indexAxis: 'y'
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading ineffective promos:', error);
            }
        }

        /**
         * Load the DataTables for unsold promotional products
         */
        function loadUnsoldProductsTable(params) {
            // Destroy the old table if it exists
            if ($.fn.DataTable.isDataTable('#unsoldProductsTable')) {
                $('#unsoldProductsTable').DataTable().destroy();
            }

            // Re-initialize the DataTable
            unsoldTable = $('#unsoldProductsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('sales.getUnsoldPromotionsList') }}?' + params.toString(),
                columns: [{
                        data: 'product_description',
                        name: 'p.product_description'
                    },
                    {
                        data: 'store_name',
                        name: 's.store_name'
                    },
                    {
                        data: 'promotion_name',
                        name: 'dp.promotion_name'
                    },
                    {
                        data: 'start_date',
                        name: 'd_start.full_date'
                    },
                    {
                        data: 'end_date',
                        name: 'd_end.full_date'
                    }
                ],
                // Apply Tailwind styling
                "dom": '<"flex justify-between"lf><"clear">rt<"flex justify-between"ip>',
                "pagingType": "simple_numbers",
                "language": {
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search records...",
                    "lengthMenu": "_MENU_ items per page",
                    "paginate": {
                        "next": "Next &rarr;",
                        "previous": "&larr; Previous"
                    }
                }
            });
        }


        // Initial Load on document ready
        $(document).ready(function() {
            // Load filters first, then load all data
            loadFilters().then(() => {
                loadAllDashboardData();
            });

            // Add event listener for the filter button
            $('#apply-filters').on('click', loadAllDashboardData);
        });
    </script>
</body>

</html>