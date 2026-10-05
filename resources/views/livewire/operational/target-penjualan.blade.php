<div>
    <section class="section">
        <div class="section-header">
            <h1>{{ $subpage }}</h1>
            @include('partials.breadcrumb')
        </div>

        <div class="row">
            {{-- Alert Message --}}
            <div class="col-12">
                @if (session()->has('success') || session()->has('danger'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-init="setTimeout(() => show = false, 4000)"
                         x-transition:leave="transition ease-in duration-500"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="alert alert-{{ session()->has('success') ? 'success' : 'danger' }} alert-dismissible show fade mb-4">
                        <div class="alert-body">
                            <button class="close" @click="show = false"><span>&times;</span></button>
                            {{ session('success') ?? session('danger') }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-12 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ $content }}</h4>
                        <div class="card-header-action">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0 font-weight-bold text-muted small"><i class="fas fa-calendar-alt mr-1"></i>Pilih Tahun:</label>
                                <select wire:model.live="year" class="form-control form-control-sm mr-3 font-weight-bold" style="width: 82px; height: 31px; font-size: 12px; border-radius: 30px; padding: 2px 8px;">
                                    @foreach ($availableYears as $yr)
                                        <option value="{{ $yr }}">{{ $yr }}</option>
                                    @endforeach
                                </select>
                                <div class="btn-group">
                                    <a href="{{ route('kasir.labor') }}" class="btn btn-primary">
                                        <i class="fas fa-users mr-1"></i> Tenaga Kerja
                                    </a>
                                    <a href="{{ route('kasir.overhead') }}" class="btn btn-warning">
                                        <i class="fas fa-file-invoice-dollar mr-1"></i> Biaya Operasional
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- FORM INPUT TARGET PENJUALAN TAHUNAN --}}
                            <div class="col-lg-5 col-md-12 mb-4">
                                <div class="card card-hero mb-0 shadow-sm border">
                                    <div class="card-header">
                                        <div class="card-icon">
                                            <i class="fas fa-bullseye"></i>
                                        </div>
                                        <h4>Target Penjualan Tahunan</h4>
                                        <div class="card-description">Konfigurasi Target Omset & Estimasi Cup untuk Tahun {{ $year }}</div>
                                    </div>
                                    <div class="card-body">
                                        <form wire:submit.prevent="saveTarget">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Tahun Target</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    </div>
                                                    <input type="number" class="form-control @error('year') is-invalid @enderror" wire:model.live="year" min="2000" max="2100">
                                                </div>
                                                @error('year') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Target Penjualan Tahunan (Rp)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Rp</span>
                                                    </div>
                                                    <input type="number" step="any" class="form-control @error('annual_sales_target') is-invalid @enderror" wire:model.live="annual_sales_target" placeholder="100000000">
                                                </div>
                                                <small class="text-muted">Contoh: Rp100.000.000</small>
                                                @error('annual_sales_target') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Estimasi Harga Jual Rata-rata per Cup</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Rp</span>
                                                    </div>
                                                    <input type="number" step="any" class="form-control @error('average_selling_price') is-invalid @enderror" wire:model.live="average_selling_price" placeholder="20000">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">/ cup</span>
                                                    </div>
                                                </div>
                                                <small class="text-info d-block mt-1">
                                                    <i class="fas fa-info-circle mr-1"></i> Digunakan untuk memperkirakan jumlah cup yang perlu terjual dan tidak mengubah harga produk.
                                                </small>
                                                @error('average_selling_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Hari Kerja Operasional per Bulan</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control @error('operating_days') is-invalid @enderror" wire:model.live="operating_days" placeholder="26">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">hari / bulan</span>
                                                    </div>
                                                </div>
                                                @error('operating_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="alert alert-light border mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small">Target Cup Tahunan:</span>
                                                    <strong class="text-primary font-weight-bold">{{ number_format($annualTargetCups, 0, ',', '.') }} cup</strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small">Target Penjualan Bulanan:</span>
                                                    <strong class="text-dark">Rp{{ number_format($monthlyTargetSales, 0, ',', '.') }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Target Cup Bulanan:</span>
                                                    <strong class="text-success font-weight-bold">±{{ number_format(ceil($monthlyTargetCups), 0, ',', '.') }} cup</strong>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                                <i class="fas fa-save mr-1"></i> Simpan Target Penjualan Tahun {{ $year }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- RINGKASAN TARGET TAHUNAN & BULANAN SERTA BIAYA NON-BAHAN --}}
                            <div class="col-lg-7 col-md-12 mb-4">
                                <div class="section-title mt-0">Hasil Perhitungan Target & Alokasi Biaya</div>
                                <p class="text-muted small">
                                    Target cup bulanan digunakan sebagai pembagi nominal gaji tenaga kerja dan biaya overhead untuk menentukan beban non-bahan per cup.
                                </p>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card card-statistic-1 shadow-sm border mb-0">
                                            <div class="card-icon bg-primary text-white">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="card-wrap">
                                                <div class="card-header">
                                                    <h4>Target Penjualan Tahunan</h4>
                                                </div>
                                                <div class="card-body">
                                                    Rp{{ number_format($breakdown['annual_sales_target'], 0, ',', '.') }}
                                                </div>
                                                <div class="small text-muted px-3 pb-2">
                                                    Target Cup: <strong>{{ number_format($breakdown['annual_cups_target'], 0, ',', '.') }} cup/tahun</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="card card-statistic-1 shadow-sm border mb-0">
                                            <div class="card-icon bg-info text-white">
                                                <i class="fas fa-calendar-day"></i>
                                            </div>
                                            <div class="card-wrap">
                                                <div class="card-header">
                                                    <h4>Target Bulanan (Rata-rata)</h4>
                                                </div>
                                                <div class="card-body text-info">
                                                    Rp{{ number_format($monthlyTargetSales, 0, ',', '.') }}
                                                </div>
                                                <div class="small text-muted px-3 pb-2">
                                                    Target Cup: <strong>±{{ number_format(ceil($monthlyTargetCups), 0, ',', '.') }} cup/bulan</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-6 mb-3">
                                        <div class="card card-statistic-1 shadow-sm border mb-0">
                                            <div class="card-icon bg-success text-white">
                                                <i class="fas fa-user-tie"></i>
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

                                    <div class="col-md-6 mb-3">
                                        <div class="card card-statistic-1 shadow-sm border mb-0">
                                            <div class="card-icon bg-warning text-white">
                                                <i class="fas fa-bolt"></i>
                                            </div>
                                            <div class="card-wrap">
                                                <div class="card-header">
                                                    <h4>Overhead (Active)</h4>
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
                                </div>

                                <div class="card bg-primary text-white shadow-sm mt-1">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1 font-weight-bold text-white"><i class="fas fa-calculator mr-2"></i>Total Biaya Non-Bahan per Cup</h6>
                                                <small class="text-white-50">Tenaga Kerja (Rp{{ number_format($laborCostPerCup, 0, ',', '.') }}) + Overhead (Rp{{ number_format($overheadCostPerCup, 0, ',', '.') }})</small>
                                            </div>
                                            <div class="text-right">
                                                <h3 class="mb-0 font-weight-bold">Rp{{ number_format($totalNonMaterialPerCup, 2, ',', '.') }}</h3>
                                                <small class="text-white-50">per cup</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RINGKASAN PENCAPAIAN TAHUNAN --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="section-title mt-0">Ringkasan Pencapaian Tahunan ({{ $year }})</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-icon bg-primary text-white">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Aktual Penjualan</h4>
                                        </div>
                                        <div class="card-body">
                                            Rp{{ number_format($breakdown['annual_sales_actual'], 0, ',', '.') }}
                                        </div>
                                        <div class="small text-muted px-3 pb-2">
                                            Sisa: <strong>Rp{{ number_format($breakdown['annual_sales_remaining'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-icon bg-{{ $breakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }} text-white">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pencapaian Omset</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ number_format($breakdown['annual_sales_percent'], 2, ',', '.') }}%
                                        </div>
                                        <div class="small text-muted px-3 pb-2">
                                            Target: Rp{{ number_format($breakdown['annual_sales_target'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-icon bg-success text-white">
                                        <i class="fas fa-coffee"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Cup Terjual</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ number_format($breakdown['annual_cups_actual'], 0, ',', '.') }} cup
                                        </div>
                                        <div class="small text-muted px-3 pb-2">
                                            Sisa: <strong>{{ number_format($breakdown['annual_cups_remaining'], 0, ',', '.') }} cup</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3 mb-3">
                                <div class="card card-statistic-1 border shadow-sm mb-0">
                                    <div class="card-icon bg-{{ $breakdown['annual_cups_percent'] >= 100 ? 'success' : 'warning' }} text-white">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pencapaian Cup</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ number_format($breakdown['annual_cups_percent'], 2, ',', '.') }}%
                                        </div>
                                        <div class="small text-muted px-3 pb-2">
                                            Target: {{ number_format($breakdown['annual_cups_target'], 0, ',', '.') }} cup
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL PENCAPAIAN BULANAN (JANUARI - DESEMBER) --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="section-title mt-0">Tabel Pencapaian Bulanan (Januari – Desember {{ $year }})</div>
                                <div class="table-responsive border rounded">
                                    <table class="table table-striped table-hover mb-0 text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-left">Bulan</th>
                                                <th>Target Penjualan</th>
                                                <th>Penjualan Aktual</th>
                                                <th>Pencapaian Omset</th>
                                                <th>Target Cup</th>
                                                <th>Cup Terjual</th>
                                                <th>Pencapaian Cup</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($breakdown['months'] as $m)
                                                <tr>
                                                    <td class="text-left font-weight-bold">
                                                        <i class="far fa-calendar-alt text-muted mr-1"></i> {{ $m['month_name'] }}
                                                    </td>
                                                    <td>Rp{{ number_format($m['target_sales'], 0, ',', '.') }}</td>
                                                    <td class="font-weight-bold text-dark">Rp{{ number_format($m['actual_sales'], 0, ',', '.') }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <div class="progress mr-2" style="width: 80px; height: 10px;">
                                                                <div class="progress-bar bg-{{ $m['sales_achievement_percent'] >= 100 ? 'success' : ($m['sales_achievement_percent'] >= 75 ? 'primary' : ($m['sales_achievement_percent'] >= 50 ? 'warning' : 'danger')) }}" 
                                                                    role="progressbar" 
                                                                    style=`width: {{ min(100, $m['sales_achievement_percent']) }}%`>
                                                                </div>
                                                            </div>
                                                            <span class="badge badge-light font-weight-bold">{{ number_format($m['sales_achievement_percent'], 1, ',', '.') }}%</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ number_format($m['target_cups'], 0, ',', '.') }} cup</td>
                                                    <td class="font-weight-bold text-dark">{{ number_format($m['actual_cups'], 0, ',', '.') }} cup</td>
                                                    <td>
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <div class="progress mr-2" style="width: 80px; height: 10px;">
                                                                <div class="progress-bar bg-{{ $m['cups_achievement_percent'] >= 100 ? 'success' : ($m['cups_achievement_percent'] >= 75 ? 'primary' : ($m['cups_achievement_percent'] >= 50 ? 'warning' : 'danger')) }}" 
                                                                    role="progressbar" 
                                                                    style=`width: {{ min(100, $m['cups_achievement_percent']) }}%`>
                                                                </div>
                                                            </div>
                                                            <span class="badge badge-light font-weight-bold">{{ number_format($m['cups_achievement_percent'], 1, ',', '.') }}%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td class="text-left">TOTAL TAHUNAN</td>
                                                <td>Rp{{ number_format($breakdown['annual_sales_target'], 0, ',', '.') }}</td>
                                                <td>Rp{{ number_format($breakdown['annual_sales_actual'], 0, ',', '.') }}</td>
                                                <td><span class="badge badge-primary">{{ number_format($breakdown['annual_sales_percent'], 2, ',', '.') }}%</span></td>
                                                <td>{{ number_format($breakdown['annual_cups_target'], 0, ',', '.') }} cup</td>
                                                <td>{{ number_format($breakdown['annual_cups_actual'], 0, ',', '.') }} cup</td>
                                                <td><span class="badge badge-success">{{ number_format($breakdown['annual_cups_percent'], 2, ',', '.') }}%</span></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
