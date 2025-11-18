<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo/bocorocco-logo-icon-only.png') }}">

    @yield('title')
    <!-- Daterangepicker CSS -->
    <link href="{{ asset('backend/vendors/daterangepicker/daterangepicker.css') }}" rel="stylesheet" type="text/css" />

    <!-- Data Table CSS -->
    <link href="{{ asset('backend/vendors/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('backend/vendors/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}"
        rel="stylesheet" type="text/css" />

    <!-- Select2 CSS -->
    <link href="{{ asset('backend/vendors/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Main CSS -->
    <link href="{{ asset('backend/dist/css/style.css') }}" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css" rel="stylesheet"
        type="text/css">

    <style>
        .space-y-3>*+*
        {
            margin-top: 1rem;
        }

        .flatpickr[readonly] {
            background-color: white;
        }

        /* Fix for main content area */
        .main-content {
            margin-top: 120px;
            padding-left: 200px;
            padding-right: 200px;
            min-height: calc(100vh - 120px);
        }

        body {
            background-color: #f5f7fa;
        }
    </style>
    @yield('styles')
</head>

<body>
    <!-- Simplified wrapper structure -->
    <div class="wrapper">
       {{-- @include('partials.header') --}}
       {{-- @include('partials.navigation') --}}
        
        <!-- Main Content Area -->
        <main class="main-content">
            @yield('content')
        </main>
        
       {{-- @include('partials.footer') --}}
    </div>

    <!-- jQuery -->
    <script src="{{ asset('backend/vendors/jquery/dist/jquery.min.js') }}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{ asset('backend/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Vue CDN JS -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.16"></script>

    <!-- FeatherIcons JS -->
    <script src="{{ asset('backend/dist/js/feather.min.js') }}"></script>

    <!-- Fancy Dropdown JS -->
    <script src="{{ asset('backend/dist/js/dropdown-bootstrap-extended.js') }}"></script>

    <!-- Simplebar JS -->
    <script src="{{ asset('backend/vendors/simplebar/dist/simplebar.min.js') }}"></script>

    <!-- Data Table JS -->
    <script src="{{ asset('backend/vendors/datatables.net/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    <!-- Daterangepicker JS -->
    <script src="{{ asset('backend/vendors/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/daterangepicker/daterangepicker.js') }}"></script>

    <!-- Amcharts Maps JS (CDN) -->
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/map.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/worldLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

    <!-- Apex JS -->
    <script src="{{ asset('backend/vendors/apexcharts/dist/apexcharts.min.js') }}"></script>

    <!-- Init JS -->
    <script src="{{ asset('backend/dist/js/init.js') }}"></script>
    <script src="{{ asset('backend/dist/js/chips-init.js') }}"></script>

    <!-- Select2 JS -->
    <script src="{{ asset('backend/vendors/select2/dist/js/select2.full.min.js') }}"></script>

    <!-- AutoNumeric JS and Vue-Autonumeric JS -->
    <script src="{{ asset('backend/dist/js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('backend/dist/js/vue-autonumeric.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>

    <script>
        let timer;

        function debounce(func, timeout = 300) {
            clearTimeout(timer);
            timer = setTimeout(func, timeout);
        }

        function makeid(length) {
            let result = '';
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            const charactersLength = characters.length;
            let counter = 0;
            while (counter < length) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
                counter += 1;
            }
            return result;
        }
    </script>

    @yield('scripts')
</body>

</html>