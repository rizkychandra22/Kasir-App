<div>
    <section class="section">
        <div class="section-header">
            <h1>{{ $subpage }}</h1>
            @include('partials.breadcrumb')
        </div>

        <div class="row">
            {{-- Alert Message Login --}}
            @if (session('info'))
                <div class="col-12" id="alert-container">
                    <div id="alert" class="alert alert-info alert-dismissible show fade mb-4">
                        <div class="alert-body">
                            {{ session('info') }}
                        </div>
                    </div>
                </div>
            @endif               
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ $content }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card card-warning h-100 mb-0">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <strong class="text-dark mb-1">Total Kategori</strong>
                                        <h4 class="mb-1">{{ $countCategory }}</h4>
                                        <p class="text-muted mb-0">Kategori produk aktif</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card card-info h-100 mb-0">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <strong class="text-dark mb-1">Produk Tersedia</strong>
                                        <h4 class="mb-1">{{ $countProductReady }}</h4>
                                        <p class="text-muted mb-0">Total stok: {{ number_format($totalStockReady, 0, ',', '.') }} item</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card card-success h-100 mb-0">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <strong class="text-dark mb-1">Produk Terjual</strong>
                                        <h4 class="mb-1">{{ number_format($countProductSold, 0, ',', '.') }}</h4>
                                        <p class="text-muted mb-0">Periode {{ $currentMonth }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card card-danger h-100 mb-0">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <strong class="text-dark mb-1">Total Pendapatan</strong>
                                        <h4 class="mb-1">Rp{{ number_format($countRevenue, 0, ',', '.') }}</h4>
                                        <p class="text-muted mb-0">
                                            <span class="text-secondary font-weight-bold">Off: Rp{{ number_format($revenueOffline, 0, ',', '.') }}</span> | 
                                            <span class="text-success font-weight-bold">On: Rp{{ number_format($revenueOnline, 0, ',', '.') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RINGKASAN BIAYA NON-BAHAN (LABOR & OVERHEAD) --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="section-title mt-0">Ringkasan Biaya Non-Bahan (Labor & Overhead)</div>
                            </div>
                            <div class="col-12 col-md-4 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-icon bg-primary text-white">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Tenaga Kerja (Active)</h4>
                                        </div>
                                        <div class="card-body">
                                            Rp{{ number_format($totalActiveLabor, 0, ',', '.') }}
                                        </div>
                                        <div class="small text-muted px-3 pb-2">
                                            Labor / Cup: <strong>Rp{{ number_format($laborCostPerCup, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-icon bg-warning text-white">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Biaya Operasional (Active)</h4>
                                        </div>
                                        <div class="card-body">
                                            Rp{{ number_format($totalActiveOverhead, 0, ',', '.') }}
                                        </div>
                                        <div class="small text-muted px-3 pb-2">
                                            Overhead / Cup: <strong>Rp{{ number_format($overheadCostPerCup, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0 bg-light">
                                    <div class="card-icon bg-info text-white">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Non-Bahan / Cup</h4>
                                        </div>
                                        <div class="card-body text-primary">
                                            Rp{{ number_format($totalNonMaterialPerCup, 2, ',', '.') }}
                                        </div>
                                        <div class="small text-muted px-3 pb-2">
                                            Target: <strong>{{ number_format($targetMonthly, 0, ',', '.') }} cup</strong> / bulan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            function initDashboardAlert() {
                setTimeout(function() {
                    var infoAlert = document.getElementById('alert');
                    if (infoAlert) {
                        infoAlert.style.transition = 'opacity 0.5s ease-out';
                        infoAlert.style.opacity = '0';
                        setTimeout(function() {
                            infoAlert.remove();
                        }, 500);
                    }
                }, 3000);
            }

            document.addEventListener('livewire:navigated', initDashboardAlert);
            document.addEventListener('DOMContentLoaded', initDashboardAlert);
        </script>
    @endpush
</div>
