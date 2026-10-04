<div>
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h1>{{ $subpage }}</h1>
            <div class="d-flex align-items-center">
                <label class="mr-2 mb-0 font-weight-bold text-muted small"><i class="fas fa-calendar mr-1"></i>Tahun:</label>
                <select wire:model.live="selectedYear" class="form-control form-control-sm font-weight-bold" style="width: 100px;">
                    @foreach ($availableYears as $yr)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ $content }} - Tahun {{ $selectedYear }}</h4>
                        <div class="card-header-action">
                            <a href="{{ route('kasir.target-penjualan') }}" class="btn btn-sm btn-info">
                                <i class="fas fa-bullseye mr-1"></i> Kelola Target Penjualan
                            </a>
                        </div>
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

                        {{-- TARGET PENJUALAN TAHUNAN --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="section-title mt-0 d-flex justify-content-between align-items-center">
                                    <span>Target Penjualan Tahunan (Tahun {{ $selectedYear }})</span>
                                    <small class="text-muted font-weight-normal">
                                        Status: {{ $annualBreakdown['annual_sales_target'] > 0 ? 'Target Terkonfigurasi' : 'Belum Diatur' }}
                                    </small>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-wrap p-3">
                                        <div class="text-muted small">Target Penjualan</div>
                                        <h5 class="mb-0 text-primary">Rp{{ number_format($annualBreakdown['annual_sales_target'], 0, ',', '.') }}</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-wrap p-3">
                                        <div class="text-muted small">Aktual Penjualan</div>
                                        <h5 class="mb-0 text-dark">Rp{{ number_format($annualBreakdown['annual_sales_actual'], 0, ',', '.') }}</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-wrap p-3">
                                        <div class="text-muted small">Pencapaian Omset</div>
                                        <h5 class="mb-0 text-{{ $annualBreakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }}">
                                            {{ number_format($annualBreakdown['annual_sales_percent'], 1, ',', '.') }}%
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-wrap p-3">
                                        <div class="text-muted small">Target Cup</div>
                                        <h5 class="mb-0 text-secondary">{{ number_format($annualBreakdown['annual_cups_target'], 0, ',', '.') }} cup</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-wrap p-3">
                                        <div class="text-muted small">Cup Terjual</div>
                                        <h5 class="mb-0 text-success">{{ number_format($annualBreakdown['annual_cups_actual'], 0, ',', '.') }} cup</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-wrap p-3">
                                        <div class="text-muted small">Sisa Target</div>
                                        <h5 class="mb-0 text-danger">Rp{{ number_format($annualBreakdown['annual_sales_remaining'], 0, ',', '.') }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RINGKASAN PENCAPAIAN BULANAN (TABEL SEDERHANA) --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="section-title mt-0">Ringkasan Pencapaian Bulanan ({{ $selectedYear }})</div>
                                <div class="table-responsive border rounded">
                                    <table class="table table-sm table-striped table-hover mb-0 text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-left">Bulan</th>
                                                <th>Target</th>
                                                <th>Aktual</th>
                                                <th>Pencapaian</th>
                                                <th>Target Cup</th>
                                                <th>Terjual</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($annualBreakdown['months'] as $m)
                                                <tr>
                                                    <td class="text-left font-weight-bold">{{ $m['month_name'] }}</td>
                                                    <td>Rp{{ number_format($m['target_sales'], 0, ',', '.') }}</td>
                                                    <td class="font-weight-bold">Rp{{ number_format($m['actual_sales'], 0, ',', '.') }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $m['sales_achievement_percent'] >= 100 ? 'success' : ($m['sales_achievement_percent'] >= 75 ? 'primary' : ($m['sales_achievement_percent'] >= 50 ? 'warning' : 'light')) }}">
                                                            {{ number_format($m['sales_achievement_percent'], 1, ',', '.') }}%
                                                        </span>
                                                    </td>
                                                    <td>{{ number_format($m['target_cups'], 0, ',', '.') }}</td>
                                                    <td class="font-weight-bold">{{ number_format($m['actual_cups'], 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td class="text-left">Total Tahunan</td>
                                                <td>Rp{{ number_format($annualBreakdown['annual_sales_target'], 0, ',', '.') }}</td>
                                                <td>Rp{{ number_format($annualBreakdown['annual_sales_actual'], 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        {{ number_format($annualBreakdown['annual_sales_percent'], 1, ',', '.') }}%
                                                    </span>
                                                </td>
                                                <td>{{ number_format($annualBreakdown['annual_cups_target'], 0, ',', '.') }}</td>
                                                <td>{{ number_format($annualBreakdown['annual_cups_actual'], 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
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
