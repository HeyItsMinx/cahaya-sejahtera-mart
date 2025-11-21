<!-- Header -->
<header class="header bg-white border-bottom fixed-top shadow-sm">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between py-3 ml-2">
            <!-- Left Section: Logo & Brand -->
            <div class="d-flex align-items-center">
                <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
                    {{-- <img src="{{ asset('logo/bocorocco-logo-icon-only.png') }}" alt="Logo" height="40" class="me-2"> --}}
                    <span class="fs-5 fw-semibold text-dark">Cahaya Sejahtera Mart</span>
                </a>
            </div>

            <!-- Center Section: Navigation -->
            <nav class="d-none d-lg-flex">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium" href="{{ route('sales.index') }}">
                            <i data-feather="trending-up" class="icon-dual icon-xs me-1"></i>
                            Sales & Promotions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium" href="{{ route('inventory.index') }}">
                            <i data-feather="package" class="icon-dual icon-xs me-1"></i>
                            Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium" href="{{ route('procurement.index') }}">
                            <i data-feather="shopping-cart" class="icon-dual icon-xs me-1"></i>
                            Procurement
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Mobile Menu Toggle -->
            <button class="btn btn-icon btn-flush-dark btn-rounded flush-soft-hover d-lg-none" 
                    type="button" 
                    data-bs-toggle="offcanvas" 
                    data-bs-target="#mobileMenu">
                <span class="icon">
                    <i data-feather="menu" class="icon-dual"></i>
                </span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Menu Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('sales.index') }}">
                    <i data-feather="trending-up" class="icon-dual icon-xs me-2"></i>
                    Sales & Promotions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('inventory.index') }}">
                    <i data-feather="package" class="icon-dual icon-xs me-2"></i>
                    Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('procurement.index') }}">
                    <i data-feather="shopping-cart" class="icon-dual icon-xs me-2"></i>
                    Procurement
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    .header {
        height: 70px;
        z-index: 1000;
    }
    
    .btn-icon {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-flush-dark:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .avatar-img {
        width: 36px;
        height: 36px;
        object-fit: cover;
    }
    
    .icon-dual {
        width: 18px;
        height: 18px;
    }
    
    .badge-soft-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    /* Adjust main content to account for fixed header */
    .main-content {
        margin-top: 90px;
    }
</style>