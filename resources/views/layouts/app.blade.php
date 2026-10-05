<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $subpage ?? 'Dashboard' }} &mdash; {{ $content ?? 'Sistem Kasir' }}</title>
    <link rel="icon" href="https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full//97/MTA-50267148/no-brand_papan-tanda-kasir-cashier-logo-sign_full01.jpg">

    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/modules/fontawesome/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/modules/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/modules/summernote/summernote-bs4.css') }}">

    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/css/components.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                    </ul>
                </div>

                {{-- @include('partials.notifications')
                    @include('partials.messages') --}}

                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="fas fa-user mr-3"></div>
                            <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->name }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="" class="dropdown-item has-icon">
                                <i class="far fa-user"></i> Profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('logout') }}" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="">Sistem Kasir</a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="">SK</a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">Dashboard {{ Auth::user()->role }}</li>
                        @if (Auth::user()->isAdmin())
                        <li @class(['active'=> request()->routeIs('admin.dashboard')])>
                            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                                <i class="fas fa-home"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        @else
                        <li class="{{ Route::is('kasir.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('kasir.dashboard') }}" class="nav-link">
                                <i class="fas fa-home"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        @endif
                        <li class="menu-header">Menu Utama {{ Auth::user()->role }}</li>
                        <li class="{{ Route::is('kasir.bahan') ? 'active' : '' }}">
                            <a href="{{ route('kasir.bahan') }}" class="nav-link">
                                <i class="fas fa-seedling"></i> <span>Data Master Bahan</span>
                            </a>
                        </li>
                        <li class="{{ Route::is(['kasir.product', 'kasir.product.export', 'kasir.category']) ? 'active' : '' }}">
                            <a href="{{ route('kasir.product') }}" class="nav-link">
                                <i class="fas fa-cubes"></i> <span>Data Produk</span>
                            </a>
                        </li>
                        <li class="{{ Route::is(['kasir.shopping', 'kasir.shopping.export']) ? 'active' : '' }}">
                            <a href="{{ route('kasir.shopping') }}" class="nav-link">
                                <i class="fas fa-money-bill-wave"></i> <span>Data Penjualan</span>
                            </a>
                        </li>

                        @if (Auth::user()->isAdmin())
                        <li class="menu-header">Biaya & Target HPP</li>
                        <li class="{{ Route::is('kasir.target-penjualan') ? 'active' : '' }}">
                            <a href="{{ route('kasir.target-penjualan') }}" class="nav-link">
                                <i class="fas fa-bullseye"></i> <span>Target Penjualan</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('kasir.labor') ? 'active' : '' }}">
                            <a href="{{ route('kasir.labor') }}" class="nav-link">
                                <i class="fas fa-users"></i> <span>Data Tenaga Kerja</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('kasir.overhead') ? 'active' : '' }}">
                            <a href="{{ route('kasir.overhead') }}" class="nav-link">
                                <i class="fas fa-file-invoice-dollar"></i> <span>Biaya Operasional</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('kasir.hpp-product') ? 'active' : '' }}">
                            <a href="{{ route('kasir.hpp-product') }}" class="nav-link">
                                <i class="fas fa-calculator"></i> <span>HPP & Margin Produk</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </aside>
            </div>

            <div class="main-content">
                @if(isset($slot))
                {{ $slot }}
                @else
                @yield('content')
                @endif
            </div>

            <footer class="main-footer">
                <div class="footer-left">
                    &copy; {{ date('Y') }} - Sistem Kasir
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('!template-stisla/dist/assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/modules/popper.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/js/stisla.js') }}"></script>

    <script src="{{ asset('!template-stisla/dist/assets/js/scripts.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/js/custom.js') }}"></script>

    @livewireScripts
    @stack('scripts')

    <script>
        function forceCleanModals() {
            try {
                $('.modal').modal('hide');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            } catch (e) {}
        }

        document.addEventListener('livewire:navigating', () => {
            forceCleanModals();
        });

        document.addEventListener('livewire:navigated', () => {
            forceCleanModals();
        });

        window.addEventListener('close-modal', event => {
            $('.modal').modal('hide');
            setTimeout(() => {
                forceCleanModals();
            }, 300);
        });

        $(document).ready(function() {
            $(document).on('hidden.bs.modal', '.modal', function() {
                if ($('.modal.show').length === 0) {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                }
            });
        });
    </script>
</body>

</html>