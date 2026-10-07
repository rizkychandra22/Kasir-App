<div>
    <section class="section">
        <div class="section-header d-flex flex-wrap justify-content-between align-items-center">
            <h1>{{ $subpage }}</h1>
            {{-- Filter Bar & Breadcrumb --}}
            <div class="d-flex flex-wrap align-items-center mt-2 mt-md-0">
                {{-- Filter Periode --}}
                <div class="btn-group btn-group-sm mr-3 my-1" role="group" aria-label="Filter Periode">
                    <button type="button" wire:click="setPeriod('today')" 
                            class="btn {{ $selectedPeriod === 'today' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Hari Ini
                    </button>
                    <button type="button" wire:click="setPeriod('week')" 
                            class="btn {{ $selectedPeriod === 'week' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Minggu Ini
                    </button>
                    <button type="button" wire:click="setPeriod('month')" 
                            class="btn {{ $selectedPeriod === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Bulan Ini
                    </button>
                    <button type="button" wire:click="setPeriod('year')" 
                            class="btn {{ $selectedPeriod === 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Tahun Ini
                    </button>
                </div>

                {{-- Filter Tahun Target --}}
                <div class="d-flex align-items-center my-1 mr-3">
                    <label class="mr-2 mb-0 font-weight-bold text-muted small"><i class="fas fa-calendar-alt mr-1"></i>Tahun Target:</label>
                    <select wire:model.live="selectedYear" class="form-control form-control-sm font-weight-bold" style="width: 95px;">
                        @foreach ($availableYears as $yr)
                            <option value="{{ $yr }}">{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                @include('partials.breadcrumb')
            </div>
        </div>

        {{-- Alert Message Login / Info --}}
        @if (session('info'))
            <div class="row">
                <div class="col-12" id="alert-container">
                    <div id="alert" class="alert alert-info alert-dismissible show fade mb-4">
                        <div class="alert-body">
                            {{ session('info') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 1. Card Statistik (Ringkasan 4 Kartu) --}}
        <div class="row">
            @include('partials.admin.card-statistic')                  
        </div>

        {{-- Hidden JSON Data for Chart.js rendering --}}
        <div id="admin-chart-payload" data-chart='@json($chartPayload)' style="display: none;"></div>

        {{-- 2. Diagram 1: Penjualan Per Bulan --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark"><i class="fas fa-chart-area mr-2 text-primary"></i>Penjualan Per Bulan (Tahun {{ $selectedYear }})</h4>
                        <div class="card-header-action text-right">
                            <span class="badge badge-light border font-weight-bold text-dark">
                                Total Penjualan {{ $selectedYear }}: Rp{{ number_format(array_sum($monthlyActualSales), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 260px;">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Row Grid: Target vs Realisasi & Metode Pembayaran --}}
        <div class="row">
            {{-- Diagram 2: Target vs Realisasi --}}
            <div class="col-lg-8 col-md-12 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark"><i class="fas fa-bullseye mr-2 text-info"></i>Target vs Realisasi Penjualan (Tahun {{ $selectedYear }})</h4>
                        <div class="card-header-action">
                            <span class="badge badge-info">
                                Pencapaian: {{ number_format($annualBreakdown['annual_sales_percent'], 1, ',', '.') }}%
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-around bg-light py-2 px-3 rounded mb-3 text-center">
                            <div class="px-2 py-1">
                                <span class="text-muted small d-block">Target Tahunan</span>
                                <strong class="text-secondary">Rp{{ number_format($annualBreakdown['annual_sales_target'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="px-2 py-1">
                                <span class="text-muted small d-block">Realisasi Tahunan</span>
                                <strong class="text-primary">Rp{{ number_format($annualBreakdown['annual_sales_actual'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="px-2 py-1">
                                <span class="text-muted small d-block">Sisa Target</span>
                                <strong class="{{ $annualBreakdown['annual_sales_remaining'] > 0 ? 'text-warning' : 'text-success' }}">
                                    Rp{{ number_format($annualBreakdown['annual_sales_remaining'], 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                        <div class="chart-container" style="position: relative; height: 250px;">
                            <canvas id="targetVsActualChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Diagram 3: Metode Pembayaran --}}
            <div class="col-lg-4 col-md-12 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0">
                    <div class="card-header">
                        <h4 class="mb-0 text-dark"><i class="fas fa-wallet mr-2 text-warning"></i>Metode Pembayaran ({{ $periodLabel }})</h4>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="chart-container mb-3" style="position: relative; height: 180px;">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>

                        {{-- Payment Legend & Values --}}
                        <div class="border rounded p-2 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-money-bill-wave text-success mr-2"></i>Cash (Tunai)</span>
                                <strong>Rp{{ number_format($cashSales, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-qrcode text-primary mr-2"></i>QRIS (Manual)</span>
                                <strong>Rp{{ number_format($qrisSales, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-globe text-warning mr-2"></i>Online Order</span>
                                <strong>Rp{{ number_format($onlineSales, 0, ',', '.') }}</strong>
                            </div>
                            <div class="border-top pt-1 mt-1 d-flex justify-content-between align-items-center small text-muted">
                                <span>Total:</span>
                                <strong class="text-dark">Rp{{ number_format($cashSales + $qrisSales + $onlineSales, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle mr-1"></i>QRIS dan Online tidak masuk ke cash drawer (kas fisik).
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Row Grid: Produk Terlaris & Stok Bahan Perlu Diperhatikan --}}
        <div class="row">
            {{-- Diagram 4: Produk Terlaris --}}
            <div class="col-lg-7 col-md-12 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark"><i class="fas fa-fire mr-2 text-danger"></i>Produk Terlaris ({{ $periodLabel }})</h4>
                        <span class="badge badge-light border">Top 5 Terjual</span>
                    </div>
                    <div class="card-body">
                        @if ($topProducts->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                <h6 class="font-weight-normal">Belum ada transaksi produk pada periode ini.</h6>
                                <p class="small text-muted mb-0">Chart akan otomatis terisi saat transaksi tercatat.</p>
                            </div>
                        @else
                            <div class="chart-container mb-3" style="position: relative; height: 180px;">
                                <canvas id="topProductsChart"></canvas>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Produk</th>
                                            <th class="text-right">Quantity Terjual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($topProducts as $index => $prod)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="font-weight-bold">{{ $prod->name_prd }}</td>
                                                <td class="text-right"><span class="badge badge-primary">{{ number_format($prod->total_qty, 0, ',', '.') }} item</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Section G: Stok Bahan Perlu Diperhatikan --}}
            <div class="col-lg-5 col-md-12 col-12 mb-4">
                <div class="card shadow-sm h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark"><i class="fas fa-boxes mr-2 text-warning"></i>Stok Bahan Perlu Diperhatikan</h4>
                        <a href="{{ route('kasir.bahan') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt mr-1"></i>Master Bahan
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <ul class="list-unstyled mb-0">
                                @forelse ($materialsAttention as $material)
                                    <li class="media mb-3 pb-2 border-bottom">
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
                                            <h6 class="media-title mb-1 font-weight-bold">{{ $material->name_bahan }}</h6>
                                            <div class="small">
                                                Sisa Stok: 
                                                <strong class="{{ $material->stock <= 0 ? 'text-danger font-weight-bold' : 'text-dark font-weight-bold' }}">
                                                    {{ number_format($material->stock, 0, ',', '.') }} {{ $material->base_unit }}
                                                </strong>
                                                @if ($material->purchase_unit && $material->purchase_unit !== $material->base_unit)
                                                    <span class="text-muted ml-2">(Satuan: {{ $material->purchase_unit }})</span>
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
                        <div class="alert alert-light border small text-muted mt-3 mb-0">
                            <i class="fas fa-info-circle mr-1 text-primary"></i><strong>Catatan:</strong> Sistem belum memiliki konfigurasi threshold minimum stok (fitur roadmap). Menampilkan master bahan aktif dengan sisa stok terendah saat ini.
                        </div>

                        {{-- TARGET PENJUALAN TAHUNAN --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="section-title mt-0 mb-3 d-flex flex-wrap justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fas fa-bullseye text-primary mr-2"></i>Target Penjualan Tahunan (Tahun {{ $selectedYear }})</span>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <div class="btn-group btn-group-sm mr-2 mb-1">
                                            <a href="{{ route('kasir.target-penjualan') }}" class="btn btn-primary">
                                                <i class="fas fa-bullseye mr-1"></i> Target Penjualan
                                            </a>
                                            <a href="{{ route('kasir.labor') }}" class="btn btn-info">
                                                <i class="fas fa-users mr-1"></i> Tenaga Kerja
                                            </a>
                                            <a href="{{ route('kasir.overhead') }}" class="btn btn-warning text-white">
                                                <i class="fas fa-file-invoice-dollar mr-1"></i> Biaya Operasional
                                            </a>
                                            <a href="{{ route('kasir.hpp-product') }}" class="btn btn-success">
                                                <i class="fas fa-calculator mr-1"></i> HPP & Margin
                                            </a>
                                        </div>
                                        <div class="mb-1">
                                            <span class="text-muted font-weight-normal small mr-2">Status:</span>
                                        <span class="badge badge-{{ $annualBreakdown['annual_sales_target'] > 0 ? 'success' : 'warning' }} font-weight-bold px-2 py-1">
                                            {{ $annualBreakdown['annual_sales_target'] > 0 ? 'Target Terkonfigurasi' : 'Belum Diatur' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Target Penjualan -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 4px solid #6777ef !important; border-radius: 10px;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="letter-spacing: 0.5px;">Target Penjualan</span>
                                            <span class="badge badge-primary rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-bullseye text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-primary" style="font-size: 1.85rem; line-height: 1.2;">
                                                Rp{{ number_format($annualBreakdown['annual_sales_target'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            Target omset penjualan tahun {{ $selectedYear }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aktual Penjualan -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 4px solid #34395e !important; border-radius: 10px;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="letter-spacing: 0.5px;">Aktual Penjualan</span>
                                            <span class="badge badge-dark rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background-color: #34395e;">
                                                <i class="fas fa-money-bill-wave text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-dark" style="font-size: 1.85rem; line-height: 1.2;">
                                                Rp{{ number_format($annualBreakdown['annual_sales_actual'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            Realisasi omset masuk tahun {{ $selectedYear }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pencapaian Omset -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" @style([
                                    'border: 1px solid #e9ecef',
                                    'border-top: 4px solid #47c363 !important' => $annualBreakdown['annual_sales_percent'] >= 100,
                                    'border-top: 4px solid #3abaf4 !important' => $annualBreakdown['annual_sales_percent'] < 100,
                                    'border-radius: 10px',
                                ])>
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="letter-spacing: 0.5px;">Pencapaian Omset</span>
                                            <span class="badge badge-{{ $annualBreakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }} rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-chart-line text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-{{ $annualBreakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }}" style="font-size: 1.85rem; line-height: 1.2;">
                                                {{ number_format($annualBreakdown['annual_sales_percent'], 1, ',', '.') }}%
                                            </div>
                                        </div>
                                        <div class="progress mt-1" style="height: 6px; border-radius: 3px; background-color: #e9ecef;">
                                            <div class="progress-bar bg-{{ $annualBreakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }}" role="progressbar" @style(['width: ' . min($annualBreakdown['annual_sales_percent'], 100) . '%'])></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Target Cup -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 4px solid #6c757d !important; border-radius: 10px;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="letter-spacing: 0.5px;">Target Cup</span>
                                            <span class="badge badge-secondary rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-coffee text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-secondary" style="font-size: 1.85rem; line-height: 1.2;">
                                                {{ number_format($annualBreakdown['annual_cups_target'], 0, ',', '.') }} cup
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            Target volume penjualan tahun {{ $selectedYear }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cup Terjual -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 4px solid #47c363 !important; border-radius: 10px;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="letter-spacing: 0.5px;">Cup Terjual</span>
                                            <span class="badge badge-success rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-coffee text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-success" style="font-size: 1.85rem; line-height: 1.2;">
                                                {{ number_format($annualBreakdown['annual_cups_actual'], 0, ',', '.') }} cup
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            @if($annualBreakdown['annual_cups_target'] > 0)
                                                {{ number_format(($annualBreakdown['annual_cups_actual'] / $annualBreakdown['annual_cups_target']) * 100, 1, ',', '.') }}% dari target cup
                                            @else
                                                Realisasi cup terjual
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sisa Target -->
                            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 4px solid #fc544b !important; border-radius: 10px;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small" style="letter-spacing: 0.5px;">Sisa Target</span>
                                            <span class="badge badge-danger rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-hourglass-half text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-danger" style="font-size: 1.85rem; line-height: 1.2;">
                                                Rp{{ number_format($annualBreakdown['annual_sales_remaining'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            @if($annualBreakdown['annual_sales_remaining'] <= 0 && $annualBreakdown['annual_sales_target'] > 0)
                                                <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Target omset telah tercapai!</span>
                                            @else
                                                Kekurangan omset penjualan
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RINGKASAN PENCAPAIAN BULANAN (TABEL SEDERHANA) --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="section-title mt-0 font-weight-bold">Ringkasan Pencapaian Bulanan ({{ $selectedYear }})</div>
                                <div class="table-responsive border rounded bg-white">
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
                                <div class="section-title mt-0 font-weight-bold">Ringkasan Biaya Non-Bahan (Labor & Overhead)</div>
                            </div>
                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 4px solid #6777ef !important; border-radius: 10px;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small">Tenaga Kerja (Active)</span>
                                            <span class="badge badge-primary rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-users text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-dark" style="font-size: 1.6rem; line-height: 1.2;">
                                                Rp{{ number_format($totalActiveLabor, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            Labor / Cup: <strong class="text-primary">Rp{{ number_format($laborCostPerCup, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 4px solid #ffa426 !important; border-radius: 10px;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small">Biaya Operasional (Active)</span>
                                            <span class="badge badge-warning rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-file-invoice-dollar text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-dark" style="font-size: 1.6rem; line-height: 1.2;">
                                                Rp{{ number_format($totalActiveOverhead, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            Overhead / Cup: <strong class="text-warning">Rp{{ number_format($overheadCostPerCup, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 4px solid #3abaf4 !important; border-radius: 10px; background-color: #f8fafc;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold text-uppercase small">Non-Bahan / Cup</span>
                                            <span class="badge badge-info rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calculator text-white"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-weight-bold text-primary" style="font-size: 1.6rem; line-height: 1.2;">
                                                Rp{{ number_format($totalNonMaterialPerCup, 2, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            Target: <strong>{{ number_format($targetMonthly, 0, ',', '.') }} cup</strong> / bulan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TRANSAKSI TERAKHIR --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="section-title mt-0 mb-0 font-weight-bold">5 Transaksi Penjualan Terakhir</div>
                                    <a href="{{ route('kasir.shopping') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-list mr-1"></i> Buka Data Penjualan
                                    </a>
                                </div>
                                <div class="table-responsive border rounded bg-white">
                                    <table class="table table-sm table-hover mb-0 text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>No. Invoice</th>
                                                <th>Tipe Penjualan</th>
                                                <th>Tanggal</th>
                                                <th>Kasir</th>
                                                <th class="text-right pr-4">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentTransactions ?? [] as $trx)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <span class="badge badge-primary font-weight-bold">{{ $trx->invoice }}</span>
                                                    </td>
                                                    <td>
                                                        @if(($trx->sales_type ?? 'offline') === 'online')
                                                            <span class="badge badge-success"><i class="fas fa-globe mr-1"></i> ONLINE ({{ strtoupper($trx->payment_method ?? 'CASH') }})</span>
                                                        @else
                                                            <span class="badge badge-secondary"><i class="fas fa-store mr-1"></i> OFFLINE ({{ strtoupper($trx->payment_method ?? 'CASH') }})</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                                    <td><code>{{ $trx->user->name ?? '-' }}</code></td>
                                                    <td class="text-right pr-4 font-weight-bold text-dark">
                                                        Rp{{ number_format($trx->total_price, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">
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
                                backgroundColor: 'rgba(19, 41, 92, 0.15)',
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
                                    backgroundColor: '#e3eaef',
                                    borderColor: '#cbd5e1',
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
                                position: 'top'
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
                                backgroundColor: totalPayments === 0 ? ['#e3eaef', '#e3eaef', '#e3eaef'] : ['#47c363', '#6777ef', '#ffa426'],
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
                                backgroundColor: '#fc544b',
                                borderColor: '#e03e36',
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