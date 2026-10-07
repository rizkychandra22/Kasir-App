<div>
    <section class="section">
        {{-- SECTION HEADER --}}
        <div class="section-header d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h1 class="font-weight-bold" style="color: #13295C;">{{ $subpage }}</h1>
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Message Login / Info --}}
        @if (session('info'))
            <div class="row">
                <div class="col-12" id="alert-container">
                    <div id="alert" class="alert alert-info alert-dismissible show fade mb-3 shadow-sm" style="border-radius: 8px;">
                        <div class="alert-body">
                            <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- LEVEL 2 — TOOLBAR FILTER & QUICK ACTIONS --}}
        <div class="card shadow-sm mb-4" style="border: 1px solid #eef2f6; border-radius: 10px;">
            <div class="card-body py-2 px-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    {{-- Filter Periode & Tahun --}}
                    <div class="d-flex flex-wrap align-items-center my-1">
                        <span class="text-muted font-weight-bold small mr-2 d-none d-sm-inline">
                            <i class="fas fa-filter mr-1" style="color: #13295C;"></i>Filter:
                        </span>
                        <div class="btn-group btn-group-sm mr-3 my-1" role="group" aria-label="Filter Periode">
                            <button type="button" wire:click="setPeriod('today')" 
                                    class="btn font-weight-bold {{ $selectedPeriod === 'today' ? 'btn-primary' : 'btn-outline-primary' }}"
                                    style="{{ $selectedPeriod === 'today' ? 'background-color: #13295C; border-color: #13295C;' : 'color: #13295C; border-color: #13295C;' }}">
                                Hari Ini
                            </button>
                            <button type="button" wire:click="setPeriod('week')" 
                                    class="btn font-weight-bold {{ $selectedPeriod === 'week' ? 'btn-primary' : 'btn-outline-primary' }}"
                                    style="{{ $selectedPeriod === 'week' ? 'background-color: #13295C; border-color: #13295C;' : 'color: #13295C; border-color: #13295C;' }}">
                                Minggu Ini
                            </button>
                            <button type="button" wire:click="setPeriod('month')" 
                                    class="btn font-weight-bold {{ $selectedPeriod === 'month' ? 'btn-primary' : 'btn-outline-primary' }}"
                                    style="{{ $selectedPeriod === 'month' ? 'background-color: #13295C; border-color: #13295C;' : 'color: #13295C; border-color: #13295C;' }}">
                                Bulan Ini
                            </button>
                            <button type="button" wire:click="setPeriod('year')" 
                                    class="btn font-weight-bold {{ $selectedPeriod === 'year' ? 'btn-primary' : 'btn-outline-primary' }}"
                                    style="{{ $selectedPeriod === 'year' ? 'background-color: #13295C; border-color: #13295C;' : 'color: #13295C; border-color: #13295C;' }}">
                                Tahun Ini
                            </button>
                        </div>

                        {{-- Filter Tahun Target --}}
                        <div class="d-flex align-items-center my-1 mr-3">
                            <label class="mr-2 mb-0 font-weight-bold text-muted small"><i class="fas fa-calendar-alt mr-1" style="color: #13295C;"></i>Tahun Target:</label>
                            <select wire:model.live="selectedYear" class="form-control form-control-sm font-weight-bold" style="width: 95px; border-radius: 6px;">
                                @foreach ($availableYears as $yr)
                                    <option value="{{ $yr }}">{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- QUICK ACTION: KELOLA OPERASIONAL --}}
                    <div class="d-flex align-items-center my-1 flex-wrap">
                        <span class="text-muted font-weight-bold small mr-2 d-none d-md-inline">
                            <i class="fas fa-th mr-1 text-secondary"></i>Kelola Operasional:
                        </span>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('kasir.target-penjualan') }}" class="btn btn-outline-primary" style="border-color: #13295C; color: #13295C;">
                                <i class="fas fa-bullseye mr-1"></i> Target
                            </a>
                            <a href="{{ route('kasir.labor') }}" class="btn btn-outline-info">
                                <i class="fas fa-users mr-1"></i> Tenaga Kerja
                            </a>
                            <a href="{{ route('kasir.overhead') }}" class="btn btn-outline-warning">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Biaya Ops
                            </a>
                            <a href="{{ route('kasir.hpp-product') }}" class="btn btn-outline-success">
                                <i class="fas fa-calculator mr-1"></i> HPP & Margin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LEVEL 1 — KPI UTAMA (4 Card Statistik) --}}
        <div class="row">
            @include('partials.admin.card-statistic')                  
        </div>

        {{-- Hidden JSON Data for Chart.js rendering --}}
        <div id="admin-chart-payload" data-chart='@json($chartPayload)' style="display: none;"></div>

        {{-- LEVEL 3 — GRAFIK UTAMA (2 Kolom Seimbang) --}}
        <div class="row">
            {{-- Grafik 1: Penjualan Per Bulan --}}
            <div class="col-lg-6 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0" style="border: 1px solid #eef2f6; border-radius: 10px;">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 1rem;">
                            <i class="fas fa-chart-line mr-2" style="color: #13295C;"></i>Penjualan Per Bulan (Tahun {{ $selectedYear }})
                        </h4>
                        <div class="card-header-action">
                            <span class="badge badge-light border font-weight-bold text-dark px-2 py-1">
                                Total: Rp{{ number_format(array_sum($monthlyActualSales), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body pt-2 pb-3">
                        <div class="chart-container" style="position: relative; height: 260px;">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grafik 2: Target vs Realisasi --}}
            <div class="col-lg-6 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0" style="border: 1px solid #eef2f6; border-radius: 10px;">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 1rem;">
                            <i class="fas fa-bullseye mr-2 text-info"></i>Target vs Realisasi (Tahun {{ $selectedYear }})
                        </h4>
                        <div class="card-header-action">
                            <span class="badge badge-info px-2 py-1 font-weight-bold">
                                Pencapaian: {{ number_format($annualBreakdown['annual_sales_percent'], 1, ',', '.') }}%
                            </span>
                        </div>
                    </div>
                    <div class="card-body pt-2 pb-3">
                        <div class="d-flex flex-wrap justify-content-around bg-light py-2 px-2 rounded mb-2 text-center" style="font-size: 12px;">
                            <div class="px-2 py-1">
                                <span class="text-muted d-block small">Target Tahunan</span>
                                <strong class="text-secondary">Rp{{ number_format($annualBreakdown['annual_sales_target'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="px-2 py-1">
                                <span class="text-muted d-block small">Realisasi Tahunan</span>
                                <strong style="color: #13295C;">Rp{{ number_format($annualBreakdown['annual_sales_actual'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="px-2 py-1">
                                <span class="text-muted d-block small">Sisa Target</span>
                                <strong class="{{ $annualBreakdown['annual_sales_remaining'] > 0 ? 'text-warning' : 'text-success' }}">
                                    Rp{{ number_format($annualBreakdown['annual_sales_remaining'], 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                        <div class="chart-container" style="position: relative; height: 210px;">
                            <canvas id="targetVsActualChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LEVEL 4 — ANALISIS PENJUALAN (Metode Pembayaran & Top 5 Produk) --}}
        <div class="row">
            {{-- Diagram 3: Metode Pembayaran --}}
            <div class="col-lg-5 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0" style="border: 1px solid #eef2f6; border-radius: 10px;">
                    <div class="card-header py-3">
                        <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 1rem;">
                            <i class="fas fa-wallet mr-2 text-warning"></i>Metode Pembayaran ({{ $periodLabel }})
                        </h4>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between pt-2 pb-3">
                        <div class="chart-container mb-3" style="position: relative; height: 180px;">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>

                        {{-- Payment Legend & Values --}}
                        <div class="border rounded p-2 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold"><i class="fas fa-money-bill-wave text-success mr-2"></i>Cash (Tunai)</span>
                                <strong class="text-dark small">Rp{{ number_format($cashSales, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold"><i class="fas fa-qrcode text-primary mr-2"></i>QRIS (Manual)</span>
                                <strong class="text-dark small">Rp{{ number_format($qrisSales, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold"><i class="fas fa-globe text-warning mr-2"></i>Online Order</span>
                                <strong class="text-dark small">Rp{{ number_format($onlineSales, 0, ',', '.') }}</strong>
                            </div>
                            <div class="border-top pt-1 mt-1 d-flex justify-content-between align-items-center small text-muted font-weight-bold">
                                <span>Total:</span>
                                <strong style="color: #13295C;">Rp{{ number_format($cashSales + $qrisSales + $onlineSales, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <small class="text-muted mt-2 d-block" style="font-size: 11px;">
                            <i class="fas fa-info-circle mr-1 text-primary"></i>QRIS dan Online tidak masuk ke cash drawer (kas fisik).
                        </small>
                    </div>
                </div>
            </div>

            {{-- Diagram 4: Produk Terlaris --}}
            <div class="col-lg-7 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0" style="border: 1px solid #eef2f6; border-radius: 10px;">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 1rem;">
                            <i class="fas fa-fire mr-2 text-danger"></i>Top 5 Produk Terlaris ({{ $periodLabel }})
                        </h4>
                        <span class="badge badge-light border small font-weight-bold text-muted">Berdasarkan Quantity</span>
                    </div>
                    <div class="card-body pt-2 pb-3">
                        @if ($topProducts->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                <h6 class="font-weight-normal">Belum ada transaksi produk pada periode ini.</h6>
                                <p class="small text-muted mb-0">Chart akan otomatis terisi saat transaksi tercatat.</p>
                            </div>
                        @else
                            <div class="chart-container mb-3" style="position: relative; height: 160px;">
                                <canvas id="topProductsChart"></canvas>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Nama Produk</th>
                                            <th class="text-right">Quantity Terjual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($topProducts as $index => $prod)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="font-weight-bold text-dark">{{ $prod->name_prd }}</td>
                                                <td class="text-right">
                                                    <span class="badge badge-primary font-weight-bold" style="background-color: #13295C;">
                                                        {{ number_format($prod->total_qty, 0, ',', '.') }} item
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- LEVEL 5 — OPERASIONAL --}}
        <div class="section-title mt-2 mb-3 font-weight-bold text-dark" style="font-size: 1.05rem;">
            <i class="fas fa-cogs mr-2" style="color: #13295C;"></i>Operasional & Target Bisnis
        </div>

        <div class="row">
            {{-- Peringatan Stok Bahan --}}
            <div class="col-lg-6 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0" style="border: 1px solid #eef2f6; border-radius: 10px;">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 1rem;">
                            <i class="fas fa-boxes mr-2 text-warning"></i>Stok Bahan Perlu Diperhatikan
                        </h4>
                        <a href="{{ route('kasir.bahan') }}" class="btn btn-sm btn-outline-primary" style="border-color: #13295C; color: #13295C;">
                            <i class="fas fa-external-link-alt mr-1"></i>Master Bahan
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between pt-2 pb-3">
                        <div>
                            <ul class="list-unstyled mb-0">
                                @forelse ($materialsAttention as $material)
                                    <li class="media mb-2 pb-2 border-bottom align-items-center">
                                        <div class="media-body">
                                            <div class="float-right text-right">
                                                @if ($material->stock <= 0)
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>Stok Habis
                                                    </span>
                                                @else
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-info-circle mr-1"></i>Stok Aktual
                                                    </span>
                                                @endif
                                            </div>
                                            <h6 class="media-title mb-1 font-weight-bold text-dark" style="font-size: 13.5px;">{{ $material->name_bahan }}</h6>
                                            <div class="small text-muted">
                                                Sisa Stok: 
                                                <strong class="{{ $material->stock <= 0 ? 'text-danger' : 'text-dark font-weight-bold' }}">
                                                    {{ number_format($material->stock, 0, ',', '.') }} {{ $material->base_unit }}
                                                </strong>
                                                @if ($material->purchase_unit && $material->purchase_unit !== $material->base_unit)
                                                    <span class="text-muted ml-1">(Satuan: {{ $material->purchase_unit }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-center text-muted py-4">
                                        Belum ada data master bahan.
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- Roadmap Note as Required by Section G --}}
                        <div class="alert alert-light border small text-muted mt-3 mb-0" style="font-size: 11px;">
                            <i class="fas fa-info-circle mr-1 text-primary"></i><strong>Catatan:</strong> Sistem belum memiliki konfigurasi threshold minimum stok (fitur roadmap). Menampilkan master bahan aktif dengan sisa stok terendah saat ini.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Biaya Non-Bahan (Labor & Overhead) --}}
            <div class="col-lg-6 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0" style="border: 1px solid #eef2f6; border-radius: 10px;">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 1rem;">
                            <i class="fas fa-calculator mr-2" style="color: #13295C;"></i>Ringkasan Biaya Non-Bahan
                        </h4>
                        <div class="card-header-action">
                            <span class="badge badge-light border text-muted small">Target: {{ number_format($targetMonthly, 0, ',', '.') }} cup / bulan</span>
                        </div>
                    </div>
                    <div class="card-body pt-3 pb-3">
                        <div class="row">
                            {{-- Tenaga Kerja --}}
                            <div class="col-12 mb-3">
                                <div class="p-3 rounded border" style="background: #fafbfe; border-left: 4px solid #13295C !important;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted font-weight-bold small text-uppercase" style="font-size: 11px;">Tenaga Kerja (Active)</span>
                                            <h5 class="mb-0 font-weight-bold text-dark mt-1">Rp{{ number_format($totalActiveLabor, 0, ',', '.') }}</h5>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-light border font-weight-bold" style="color: #13295C; font-size: 12px;">
                                                Rp{{ number_format($laborCostPerCup, 2, ',', '.') }} <small class="text-muted">/cup</small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Biaya Operasional --}}
                            <div class="col-12 mb-3">
                                <div class="p-3 rounded border" style="background: #fafbfe; border-left: 4px solid #ffc107 !important;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted font-weight-bold small text-uppercase" style="font-size: 11px;">Biaya Operasional (Active)</span>
                                            <h5 class="mb-0 font-weight-bold text-dark mt-1">Rp{{ number_format($totalActiveOverhead, 0, ',', '.') }}</h5>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-light border text-warning font-weight-bold" style="font-size: 12px;">
                                                Rp{{ number_format($overheadCostPerCup, 2, ',', '.') }} <small class="text-muted">/cup</small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Total Non-Bahan --}}
                            <div class="col-12">
                                <div class="p-3 rounded border" style="background: #eef4fc; border-left: 4px solid #17a2b8 !important;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="font-weight-bold small text-uppercase text-info" style="font-size: 11px;">Total Non-Bahan / Cup</span>
                                            <h5 class="mb-0 font-weight-bold mt-1" style="color: #13295C;">Rp{{ number_format($totalNonMaterialPerCup, 2, ',', '.') }}</h5>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-info font-weight-bold px-2 py-1">Labor + Overhead</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Target Penjualan Tahunan Cards & Ringkasan Bulanan --}}
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm mb-0" style="border: 1px solid #eef2f6; border-radius: 10px;">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center py-3">
                        <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 1rem;">
                            <i class="fas fa-bullseye mr-2" style="color: #13295C;"></i>Target Penjualan Tahunan (Tahun {{ $selectedYear }})
                        </h4>
                        <div>
                            <span class="text-muted small mr-2">Status:</span>
                            <span class="badge badge-{{ $annualBreakdown['annual_sales_target'] > 0 ? 'success' : 'warning' }} font-weight-bold px-2 py-1">
                                {{ $annualBreakdown['annual_sales_target'] > 0 ? 'Target Terkonfigurasi' : 'Belum Diatur' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body pt-3 pb-3">
                        {{-- 6 Metric Cards --}}
                        <div class="row">
                            <!-- Target Penjualan -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 3px solid #13295C !important; border-radius: 8px;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="font-size: 11px;">Target Penjualan</span>
                                            <i class="fas fa-bullseye" style="color: #13295C;"></i>
                                        </div>
                                        <div class="my-1">
                                            <div class="font-weight-bold" style="color: #13295C; font-size: 1.4rem; line-height: 1.2;">
                                                Rp{{ number_format($annualBreakdown['annual_sales_target'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small" style="font-size: 11.5px;">Target omset tahun {{ $selectedYear }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aktual Penjualan -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 3px solid #34395e !important; border-radius: 8px;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="font-size: 11px;">Aktual Penjualan</span>
                                            <i class="fas fa-money-bill-wave text-dark"></i>
                                        </div>
                                        <div class="my-1">
                                            <div class="font-weight-bold text-dark" style="font-size: 1.4rem; line-height: 1.2;">
                                                Rp{{ number_format($annualBreakdown['annual_sales_actual'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small" style="font-size: 11.5px;">Realisasi masuk tahun {{ $selectedYear }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pencapaian Omset -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 3px solid {{ $annualBreakdown['annual_sales_percent'] >= 100 ? '#28a745' : '#17a2b8' }} !important; border-radius: 8px;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="font-size: 11px;">Pencapaian Omset</span>
                                            <i class="fas fa-chart-line text-{{ $annualBreakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }}"></i>
                                        </div>
                                        <div class="my-1">
                                            <div class="font-weight-bold text-{{ $annualBreakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }}" style="font-size: 1.4rem; line-height: 1.2;">
                                                {{ number_format($annualBreakdown['annual_sales_percent'], 1, ',', '.') }}%
                                            </div>
                                        </div>
                                        <div class="progress mt-1" style="height: 5px; border-radius: 3px; background-color: #e9ecef;">
                                            <div class="progress-bar bg-{{ $annualBreakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }}" role="progressbar" style="width: {{ min($annualBreakdown['annual_sales_percent'], 100) }}%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Target Cup -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 3px solid #6c757d !important; border-radius: 8px;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="font-size: 11px;">Target Cup</span>
                                            <i class="fas fa-coffee text-secondary"></i>
                                        </div>
                                        <div class="my-1">
                                            <div class="font-weight-bold text-secondary" style="font-size: 1.4rem; line-height: 1.2;">
                                                {{ number_format($annualBreakdown['annual_cups_target'], 0, ',', '.') }} cup
                                            </div>
                                        </div>
                                        <div class="text-muted small" style="font-size: 11.5px;">Target volume tahun {{ $selectedYear }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cup Terjual -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 3px solid #28a745 !important; border-radius: 8px;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="font-size: 11px;">Cup Terjual</span>
                                            <i class="fas fa-coffee text-success"></i>
                                        </div>
                                        <div class="my-1">
                                            <div class="font-weight-bold text-success" style="font-size: 1.4rem; line-height: 1.2;">
                                                {{ number_format($annualBreakdown['annual_cups_actual'], 0, ',', '.') }} cup
                                            </div>
                                        </div>
                                        <div class="text-muted small" style="font-size: 11.5px;">
                                            @if($annualBreakdown['annual_cups_target'] > 0)
                                                {{ number_format(($annualBreakdown['annual_cups_actual'] / $annualBreakdown['annual_cups_target']) * 100, 1, ',', '.') }}% dari target
                                            @else
                                                Realisasi cup terjual
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sisa Target -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 3px solid #dc3545 !important; border-radius: 8px;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="font-size: 11px;">Sisa Target</span>
                                            <i class="fas fa-hourglass-half text-danger"></i>
                                        </div>
                                        <div class="my-1">
                                            <div class="font-weight-bold text-danger" style="font-size: 1.4rem; line-height: 1.2;">
                                                Rp{{ number_format($annualBreakdown['annual_sales_remaining'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small" style="font-size: 11.5px;">
                                            @if($annualBreakdown['annual_sales_remaining'] <= 0 && $annualBreakdown['annual_sales_target'] > 0)
                                                <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Target omset tercapai!</span>
                                            @else
                                                Kekurangan omset penjualan
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tabel Ringkasan Pencapaian Bulanan --}}
                        <div class="mt-3">
                            <h6 class="font-weight-bold text-dark mb-2" style="font-size: 13.5px;">
                                <i class="fas fa-calendar-alt mr-2" style="color: #13295C;"></i>Ringkasan Pencapaian Bulanan ({{ $selectedYear }})
                            </h6>
                            <div class="table-responsive border rounded bg-white">
                                <table class="table table-sm table-striped table-hover mb-0 text-center" style="font-size: 13px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-left py-2">Bulan</th>
                                            <th class="py-2">Target</th>
                                            <th class="py-2">Aktual</th>
                                            <th class="py-2">Pencapaian</th>
                                            <th class="py-2">Target Cup</th>
                                            <th class="py-2">Terjual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($annualBreakdown['months'] as $m)
                                            <tr>
                                                <td class="text-left font-weight-bold text-dark py-2">{{ $m['month_name'] }}</td>
                                                <td class="py-2">Rp{{ number_format($m['target_sales'], 0, ',', '.') }}</td>
                                                <td class="font-weight-bold py-2" style="color: #13295C;">Rp{{ number_format($m['actual_sales'], 0, ',', '.') }}</td>
                                                <td class="py-2">
                                                    <span class="badge badge-{{ $m['sales_achievement_percent'] >= 100 ? 'success' : ($m['sales_achievement_percent'] >= 75 ? 'primary' : ($m['sales_achievement_percent'] >= 50 ? 'warning' : 'light')) }}" style="{{ $m['sales_achievement_percent'] >= 75 && $m['sales_achievement_percent'] < 100 ? 'background-color: #13295C;' : '' }}">
                                                        {{ number_format($m['sales_achievement_percent'], 1, ',', '.') }}%
                                                    </span>
                                                </td>
                                                <td class="py-2">{{ number_format($m['target_cups'], 0, ',', '.') }}</td>
                                                <td class="font-weight-bold text-dark py-2">{{ number_format($m['actual_cups'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td class="text-left py-2">Total Tahunan</td>
                                            <td class="py-2">Rp{{ number_format($annualBreakdown['annual_sales_target'], 0, ',', '.') }}</td>
                                            <td class="py-2" style="color: #13295C;">Rp{{ number_format($annualBreakdown['annual_sales_actual'], 0, ',', '.') }}</td>
                                            <td class="py-2">
                                                <span class="badge badge-primary font-weight-bold" style="background-color: #13295C;">
                                                    {{ number_format($annualBreakdown['annual_sales_percent'], 1, ',', '.') }}%
                                                </span>
                                            </td>
                                            <td class="py-2">{{ number_format($annualBreakdown['annual_cups_target'], 0, ',', '.') }}</td>
                                            <td class="py-2 text-dark">{{ number_format($annualBreakdown['annual_cups_actual'], 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LEVEL 6 — TRANSAKSI TERAKHIR (Bottom) --}}
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm mb-0" style="border: 1px solid #eef2f6; border-radius: 10px;">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center py-3">
                        <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 1rem;">
                            <i class="fas fa-history mr-2" style="color: #13295C;"></i>5 Transaksi Penjualan Terakhir
                        </h4>
                        <a href="{{ route('kasir.shopping') }}" class="btn btn-sm btn-outline-primary" style="border-color: #13295C; color: #13295C;">
                            <i class="fas fa-list mr-1"></i> Buka Data Penjualan
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 text-center" style="font-size: 13px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50" class="py-2">#</th>
                                        <th class="py-2">No. Invoice</th>
                                        <th class="py-2">Tipe Penjualan</th>
                                        <th class="py-2">Tanggal</th>
                                        <th class="py-2">Kasir</th>
                                        <th class="text-right pr-4 py-2">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTransactions ?? [] as $trx)
                                        <tr>
                                            <td class="py-2">{{ $loop->iteration }}</td>
                                            <td class="py-2">
                                                <span class="badge badge-primary font-weight-bold" style="background-color: #13295C;">{{ $trx->invoice }}</span>
                                            </td>
                                            <td class="py-2">
                                                @if(($trx->sales_type ?? 'offline') === 'online')
                                                    <span class="badge badge-success"><i class="fas fa-globe mr-1"></i> ONLINE ({{ strtoupper($trx->payment_method ?? 'CASH') }})</span>
                                                @else
                                                    <span class="badge badge-secondary"><i class="fas fa-store mr-1"></i> OFFLINE ({{ strtoupper($trx->payment_method ?? 'CASH') }})</span>
                                                @endif
                                            </td>
                                            <td class="py-2 text-muted">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="py-2"><code>{{ $trx->user->name ?? '-' }}</code></td>
                                            <td class="text-right pr-4 font-weight-bold text-dark py-2">
                                                Rp{{ number_format($trx->total_price, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-receipt fa-2x mb-2 d-block text-secondary"></i>
                                                Belum ada transaksi penjualan tercatat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="{{ asset('!template-stisla/dist/assets/modules/chart.min.js') }}"></script>
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

            var adminCharts = {
                monthly: null,
                target: null,
                payment: null,
                products: null
            };

            function renderAdminDashboardCharts() {
                var payloadEl = document.getElementById('admin-chart-payload');
                if (!payloadEl || typeof Chart === 'undefined') return;

                var data;
                try {
                    data = JSON.parse(payloadEl.getAttribute('data-chart'));
                } catch (e) {
                    console.error('Error parsing chart data', e);
                    return;
                }

                // 1. Monthly Sales Chart (Line Chart)
                var ctxMonthly = document.getElementById('monthlySalesChart');
                if (ctxMonthly) {
                    if (adminCharts.monthly) {
                        adminCharts.monthly.destroy();
                    }
                    adminCharts.monthly = new Chart(ctxMonthly.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: data.monthlySales.labels,
                            datasets: [{
                                label: 'Penjualan',
                                data: data.monthlySales.data,
                                backgroundColor: 'rgba(19, 41, 92, 0.12)',
                                borderColor: '#13295C',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#13295C',
                                pointRadius: 4,
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(val) {
                                            return 'Rp' + Number(val).toLocaleString('id-ID');
                                        }
                                    },
                                    gridLines: {
                                        color: '#f0f0f0',
                                        drawBorder: false
                                    }
                                }],
                                xAxes: [{
                                    gridLines: {
                                        display: false
                                    }
                                }]
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(item) {
                                        return 'Penjualan: Rp' + Number(item.yLabel).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    });
                }

                // 2. Target vs Realisasi (Grouped Bar Chart)
                var ctxTarget = document.getElementById('targetVsActualChart');
                if (ctxTarget) {
                    if (adminCharts.target) {
                        adminCharts.target.destroy();
                    }
                    adminCharts.target = new Chart(ctxTarget.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: data.targetVsActual.labels,
                            datasets: [
                                {
                                    label: 'Target Penjualan',
                                    data: data.targetVsActual.targetData,
                                    backgroundColor: '#cbd5e1',
                                    borderColor: '#94a3b8',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Realisasi Penjualan',
                                    data: data.targetVsActual.actualData,
                                    backgroundColor: '#13295C',
                                    borderColor: '#0d1d42',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    fontSize: 11
                                }
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(val) {
                                            return 'Rp' + Number(val).toLocaleString('id-ID');
                                        }
                                    },
                                    gridLines: {
                                        color: '#f0f0f0',
                                        drawBorder: false
                                    }
                                }],
                                xAxes: [{
                                    gridLines: {
                                        display: false
                                    }
                                }]
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, chartData) {
                                        var datasetLabel = chartData.datasets[tooltipItem.datasetIndex].label || '';
                                        return datasetLabel + ': Rp' + Number(tooltipItem.yLabel).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    });
                }

                // 3. Payment Method Chart (Donut / Doughnut)
                var ctxPayment = document.getElementById('paymentMethodChart');
                if (ctxPayment) {
                    if (adminCharts.payment) {
                        adminCharts.payment.destroy();
                    }
                    var totalPayments = data.paymentMethod.data.reduce(function(a, b) { return a + b; }, 0);
                    adminCharts.payment = new Chart(ctxPayment.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: data.paymentMethod.labels,
                            datasets: [{
                                data: totalPayments === 0 ? [1, 0, 0] : data.paymentMethod.data,
                                backgroundColor: totalPayments === 0 ? ['#e3eaef', '#e3eaef', '#e3eaef'] : ['#28a745', '#17a2b8', '#ffc107'],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, chartData) {
                                        if (totalPayments === 0) {
                                            return 'Belum ada transaksi: Rp0';
                                        }
                                        var label = chartData.labels[tooltipItem.index] || '';
                                        var val = chartData.datasets[0].data[tooltipItem.index] || 0;
                                        return label + ': Rp' + Number(val).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    });
                }

                // 4. Top Products (Horizontal Bar Chart)
                var ctxProducts = document.getElementById('topProductsChart');
                if (ctxProducts && data.topProducts.labels && data.topProducts.labels.length > 0) {
                    if (adminCharts.products) {
                        adminCharts.products.destroy();
                    }
                    adminCharts.products = new Chart(ctxProducts.getContext('2d'), {
                        type: 'horizontalBar',
                        data: {
                            labels: data.topProducts.labels,
                            datasets: [{
                                label: 'Qty Terjual',
                                data: data.topProducts.data,
                                backgroundColor: '#13295C',
                                borderColor: '#0d1d42',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                xAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        stepSize: 1
                                    },
                                    gridLines: {
                                        color: '#f0f0f0',
                                        drawBorder: false
                                    }
                                }],
                                yAxes: [{
                                    gridLines: {
                                        display: false
                                    }
                                }]
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(item) {
                                        return 'Terjual: ' + Number(item.xLabel) + ' item';
                                    }
                                }
                            }
                        }
                    });
                }
            }

            document.addEventListener('livewire:navigated', function() {
                initDashboardAlert();
                renderAdminDashboardCharts();
            });

            document.addEventListener('DOMContentLoaded', function() {
                initDashboardAlert();
                renderAdminDashboardCharts();
            });

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('morph.updated', function() {
                    renderAdminDashboardCharts();
                });
            }
        </script>
    @endpush
</div>