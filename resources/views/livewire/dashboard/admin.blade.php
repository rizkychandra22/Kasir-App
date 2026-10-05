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
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                        <h4 class="mb-2 mb-md-0 d-inline-flex align-items-center">
                            <span>{{ $content }} - Tahun</span>
                            <select wire:model.live="selectedYear" class="custom-select custom-select-sm font-weight-bold ml-2 shadow-sm" style="width: 92px; height: 36px; padding: 4px 26px 4px 10px !important; font-size: 14px; line-height: 1.5 !important; border-radius: 6px; cursor: pointer; color: #495057; vertical-align: middle;">
                                @foreach ($availableYears as $yr)
                                    <option value="{{ $yr }}">{{ $yr }}</option>
                                @endforeach
                            </select>
                        </h4>
                        <div class="card-header-action">
                            <div class="btn-group">
                                <a href="{{ route('kasir.target-penjualan') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-bullseye mr-1"></i> Target Penjualan
                                </a>
                                <a href="{{ route('kasir.labor') }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-users mr-1"></i> Tenaga Kerja
                                </a>
                                <a href="{{ route('kasir.overhead') }}" class="btn btn-sm btn-warning text-white">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Biaya Operasional
                                </a>
                                <a href="{{ route('kasir.hpp-product') }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-calculator mr-1"></i> HPP & Margin
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- 4 CARD STATISTIK UTAMA --}}
                        <div class="row">
                            <div class="col-12 col-sm-6 col-xl-3 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 5px solid #ffa426 !important; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-uppercase font-weight-bold text-muted small" style="letter-spacing: 0.5px;">Total Kategori</span>
                                            <span class="badge badge-warning text-white rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-tags"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="text-dark font-weight-bold" style="font-size: 2.5rem; line-height: 1.15; letter-spacing: -0.5px;">
                                                {{ $countCategory }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1 d-flex align-items-center">
                                            <i class="fas fa-check-circle text-success mr-1"></i>
                                            <span>Kategori produk aktif</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 5px solid #3abaf4 !important; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-uppercase font-weight-bold text-muted small" style="letter-spacing: 0.5px;">Produk Tersedia</span>
                                            <span class="badge badge-info text-white rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-boxes"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="text-dark font-weight-bold" style="font-size: 2.5rem; line-height: 1.15; letter-spacing: -0.5px;">
                                                {{ $countProductReady }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1 d-flex align-items-center">
                                            <i class="fas fa-cubes text-info mr-1"></i>
                                            <span>Total stok: {{ number_format($totalStockReady, 0, ',', '.') }} item</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 5px solid #47c363 !important; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-uppercase font-weight-bold text-muted small" style="letter-spacing: 0.5px;">Produk Terjual</span>
                                            <span class="badge badge-success text-white rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-shopping-bag"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="text-dark font-weight-bold" style="font-size: 2.5rem; line-height: 1.15; letter-spacing: -0.5px;">
                                                {{ number_format($countProductSold, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1 d-flex align-items-center">
                                            <i class="far fa-calendar-alt text-success mr-1"></i>
                                            <span>Periode {{ $currentMonth }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3 mb-4">
                                <div class="card h-100 mb-0 shadow-sm" style="border: 1px solid #e9ecef; border-top: 5px solid #fc544b !important; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-uppercase font-weight-bold text-muted small" style="letter-spacing: 0.5px;">Total Pendapatan</span>
                                            <span class="badge badge-danger text-white rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-wallet"></i>
                                            </span>
                                        </div>
                                        <div class="my-2">
                                            <div class="text-dark font-weight-bold" style="font-size: clamp(1.6rem, 2.2vw, 2.35rem); line-height: 1.15; letter-spacing: -0.5px;">
                                                Rp{{ number_format($countRevenue, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="mt-1 d-flex align-items-center justify-content-between flex-wrap" style="font-size: 0.85rem;">
                                            <span class="d-inline-flex align-items-center mr-1">
                                                <span class="badge badge-dark text-white font-weight-bold px-2 py-1 mr-1" style="font-size: 10.5px; background-color: #34395e;">
                                                    Off
                                                </span>
                                                <strong class="text-dark">Rp{{ number_format($revenueOffline, 0, ',', '.') }}</strong>
                                            </span>
                                            <span class="d-inline-flex align-items-center">
                                                <span class="badge badge-success text-white font-weight-bold px-2 py-1 mr-1" style="font-size: 10.5px; background-color: #28a745;">
                                                    On
                                                </span>
                                                <strong class="text-success">Rp{{ number_format($revenueOnline, 0, ',', '.') }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TARGET PENJUALAN TAHUNAN --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="section-title mt-0 mb-3 d-flex flex-wrap justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fas fa-bullseye text-primary mr-2"></i>Target Penjualan Tahunan (Tahun {{ $selectedYear }})</span>
                                    <div>
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