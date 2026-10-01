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
                            <div class="btn-group">
                                <a href="{{ route('kasir.labor') }}" class="btn btn-primary">
                                    <i class="fas fa-users mr-1"></i> Data Tenaga Kerja
                                </a>
                                <a href="{{ route('kasir.overhead') }}" class="btn btn-warning">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Biaya Operasional
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- FORM INPUT TARGET PENJUALAN --}}
                            <div class="col-md-5">
                                <div class="card card-hero mb-4">
                                    <div class="card-header">
                                        <div class="card-icon">
                                            <i class="fas fa-bullseye"></i>
                                        </div>
                                        <h4>Form Target Penjualan</h4>
                                        <div class="card-description">Tentukan target volume penjualan bulanan dan hari operasional store</div>
                                    </div>
                                    <div class="card-body">
                                        <form wire:submit.prevent="saveTarget">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Target Penjualan Bulanan (cup / porsi)</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" class="form-control @error('target_sales_monthly') is-invalid @enderror" wire:model.live="target_sales_monthly" placeholder="2000">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">cup / bulan</span>
                                                    </div>
                                                </div>
                                                @error('target_sales_monthly') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Jumlah Hari Kerja per Bulan</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control @error('operating_days') is-invalid @enderror" wire:model.live="operating_days" placeholder="26">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">hari / bulan</span>
                                                    </div>
                                                </div>
                                                @error('operating_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="alert alert-light border mb-3">
                                                <i class="fas fa-calculator text-primary mr-1"></i> Target Penjualan per Hari: <strong class="text-dark">{{ number_format($dailyTarget, 2, ',', '.') }} cup / hari</strong>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                                <i class="fas fa-save mr-1"></i> Simpan Target Penjualan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- RINGKASAN BIAYA NON-BAHAN (OVERHEAD & LABOR SUMMARY) --}}
                            <div class="col-md-7">
                                <div class="section-title mt-0">Ringkasan Biaya Non-Bahan per Cup</div>
                                <p class="text-muted">Estimasi alokasi biaya tenaga kerja & operasional overhead untuk setiap porsi/cup produk berdasarkan target penjualan {{ number_format($target_sales_monthly, 0, ',', '.') }} cup/bulan.</p>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card card-statistic-1 shadow-sm border mb-0">
                                            <div class="card-icon bg-primary text-white">
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
                                                    <strong>Rp{{ number_format($laborCostPerCup, 2, ',', '.') }}</strong> / cup
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
                                                    <h4>Biaya Operasional (Active)</h4>
                                                </div>
                                                <div class="card-body">
                                                    Rp{{ number_format($totalActiveOverhead, 0, ',', '.') }}
                                                </div>
                                                <div class="small text-muted px-3 pb-2">
                                                    <strong>Rp{{ number_format($overheadCostPerCup, 2, ',', '.') }}</strong> / cup
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card bg-primary text-white shadow-sm mt-2">
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

                                <div class="alert alert-info mt-3 py-2 small">
                                    <i class="fas fa-info-circle mr-1"></i> <strong>Catatan Logic:</strong> Tenaga kerja & operasional bertipe <strong>Non-Aktif</strong> tidak dimasukkan ke dalam perhitungan pembagian biaya per cup.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
