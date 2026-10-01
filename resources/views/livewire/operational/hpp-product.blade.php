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
                                <a href="{{ route('kasir.product') }}" class="btn btn-warning">
                                    <i class="fas fa-cubes mr-1"></i> Data Produk
                                </a>
                                <a href="{{ route('kasir.target-penjualan') }}" class="btn btn-info">
                                    <i class="fas fa-bullseye mr-1"></i> Target Penjualan
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- FILTER & SEARCH BAR --}}
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2">
                                <select class="form-control" wire:model.live="selectedCategoryId">
                                    <option value="">-- Semuanya Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 mb-2">
                                <input type="text" class="form-control" wire:model.live="search" placeholder="Cari nama atau kode produk...">
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold">Target Margin</span>
                                    </div>
                                    <input type="number" step="any" min="0" max="99" class="form-control text-center font-weight-bold" wire:model.live="targetMarginPercent">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL MASTER HPP PRODUK --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th class="text-center" style="width: 4%">#</th>
                                        <th>Nama Produk</th>
                                        <th class="text-right">HPP Bahan</th>
                                        <th class="text-right">Labor/Cup</th>
                                        <th class="text-right">Overhead/Cup</th>
                                        <th class="text-right bg-info text-white">HPP TOTAL</th>
                                        <th class="text-right">Harga Offline</th>
                                        <th class="text-center">Margin Off</th>
                                        <th class="text-right">Harga Online</th>
                                        <th class="text-center">Margin On</th>
                                        <th class="text-center" style="width: 10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $index => $prd)
                                        @php
                                            $hppInfo = $prd->getHppDetails();
                                            $hppTotal = $hppInfo['hpp_total'];

                                            $offPrice = $prd->price_offline ?? $prd->price;
                                            $offNominal = \App\Services\HppService::calculateMarginNominal($offPrice, $hppTotal);
                                            $offPercent = \App\Services\HppService::calculateMarginPercent($offPrice, $hppTotal);
                                            $offStatus = \App\Services\HppService::getMarginStatus($offPercent, $validatedTargetMargin);

                                            $onPrice = $prd->price_online ?? $prd->price;
                                            $onNominal = \App\Services\HppService::calculateMarginNominal($onPrice, $hppTotal);
                                            $onPercent = \App\Services\HppService::calculateMarginPercent($onPrice, $hppTotal);
                                            $onStatus = \App\Services\HppService::getMarginStatus($onPercent, $validatedTargetMargin);
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $products->firstItem() + $index }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $prd->name_prd }}</strong>
                                                <div class="small text-muted">{{ $prd->category->name ?? '-' }} | Kode: {{ $prd->code_prd }}</div>
                                                @if(!empty($hppInfo['warnings']))
                                                    @foreach($hppInfo['warnings'] as $w)
                                                        <span class="badge badge-warning text-dark py-0 px-1 font-weight-normal small mt-1">{{ $w }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td class="text-right">Rp{{ number_format($hppInfo['hpp_bahan'], 0, ',', '.') }}</td>
                                            <td class="text-right">Rp{{ number_format($hppInfo['labor_cost_per_cup'], 0, ',', '.') }}</td>
                                            <td class="text-right">Rp{{ number_format($hppInfo['overhead_cost_per_cup'], 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold text-primary bg-light">Rp{{ number_format($hppTotal, 0, ',', '.') }}</td>
                                            
                                            {{-- Offline --}}
                                            <td class="text-right font-weight-bold">Rp{{ number_format($offPrice, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $offPercent >= $validatedTargetMargin ? 'badge-success' : 'badge-danger' }}">
                                                    {{ number_format($offPercent, 1, ',', '.') }}%
                                                </span>
                                                <div class="small text-muted">Rp{{ number_format($offNominal, 0, ',', '.') }}</div>
                                            </td>

                                            {{-- Online --}}
                                            <td class="text-right font-weight-bold">Rp{{ number_format($onPrice, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $onPercent >= $validatedTargetMargin ? 'badge-success' : 'badge-danger' }}">
                                                    {{ number_format($onPercent, 1, ',', '.') }}%
                                                </span>
                                                <div class="small text-muted">Rp{{ number_format($onNominal, 0, ',', '.') }}</div>
                                            </td>

                                            <td class="text-center">
                                                <button type="button" wire:click="selectProduct({{ $prd->id }})" class="btn btn-sm btn-info" title="Lihat Detail HPP & Margin">
                                                    <i class="fas fa-eye mr-1"></i> Detail
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-4 text-muted">Belum ada produk yang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAIL MODAL / PANEL HARGA & HPP PRODUK --}}
        @if($selectedProduct && $selectedHppDetails)
            <div class="modal fade show d-block" id="modalHppDetail" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white py-3">
                            <h5 class="modal-title text-white">
                                <i class="fas fa-calculator mr-2"></i> Rincian HPP Total & Analisis Margin: {{ $selectedProduct->name_prd }}
                            </h5>
                            <button type="button" class="close text-white" wire:click="closeDetail"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            {{-- BAGIAN 1: STRUKTUR BIAYA HPP TOTAL --}}
                            <div class="card card-hero mb-3 border">
                                <div class="card-header py-2 bg-light">
                                    <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-layer-group text-primary mr-2"></i>Struktur Biaya & HPP Total</h6>
                                </div>
                                <div class="card-body py-3">
                                    <div class="row text-center">
                                        <div class="col-md-3 border-right">
                                            <small class="text-muted d-block">HPP Bahan</small>
                                            <strong class="h6 mb-0 text-dark">Rp{{ number_format($selectedHppDetails['hpp_bahan'], 0, ',', '.') }}</strong>
                                            <div class="small text-muted">({{ number_format($selectedHppDetails['contrib_bahan'], 1, ',', '.') }}%)</div>
                                        </div>
                                        <div class="col-md-3 border-right">
                                            <small class="text-muted d-block">Biaya Tenaga Kerja</small>
                                            <strong class="h6 mb-0 text-dark">Rp{{ number_format($selectedHppDetails['labor_cost_per_cup'], 0, ',', '.') }}</strong>
                                            <div class="small text-muted">({{ number_format($selectedHppDetails['contrib_labor'], 1, ',', '.') }}%)</div>
                                        </div>
                                        <div class="col-md-3 border-right">
                                            <small class="text-muted d-block">Biaya Overhead</small>
                                            <strong class="h6 mb-0 text-dark">Rp{{ number_format($selectedHppDetails['overhead_cost_per_cup'], 0, ',', '.') }}</strong>
                                            <div class="small text-muted">({{ number_format($selectedHppDetails['contrib_overhead'], 1, ',', '.') }}%)</div>
                                        </div>
                                        <div class="col-md-3 bg-primary text-white rounded py-2">
                                            <small class="text-white-50 d-block">HPP TOTAL</small>
                                            <strong class="h5 mb-0 text-white font-weight-bold">Rp{{ number_format($selectedHppDetails['hpp_total'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BAGIAN 2: MARGIN OFFLINE vs ONLINE --}}
                            <div class="row">
                                {{-- OFFLINE --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card border h-100 mb-0">
                                        <div class="card-header bg-secondary py-2 text-dark font-weight-bold">
                                            <i class="fas fa-store mr-1 text-primary"></i> Penjualan OFFLINE
                                        </div>
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Harga Jual Offline:</span>
                                                <strong class="text-dark">Rp{{ number_format($offlineAnalysis['price'], 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>HPP Total:</span>
                                                <span class="text-muted">Rp{{ number_format($selectedHppDetails['hpp_total'], 0, ',', '.') }}</span>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Margin Nominal:</span>
                                                <strong class="text-success">Rp{{ number_format($offlineAnalysis['margin_nominal'], 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Margin Persentase:</span>
                                                <strong class="text-primary">{{ number_format($offlineAnalysis['margin_percent'], 2, ',', '.') }}%</strong>
                                            </div>
                                            <div class="alert alert-{{ $offlineAnalysis['margin_percent'] >= $validatedTargetMargin ? 'success' : 'warning' }} py-1 px-2 mb-0 small text-center">
                                                <strong>Status:</strong> {{ $offlineAnalysis['status'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ONLINE --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card border h-100 mb-0">
                                        <div class="card-header bg-secondary py-2 text-dark font-weight-bold">
                                            <i class="fas fa-globe mr-1 text-success"></i> Penjualan ONLINE
                                        </div>
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Harga Jual Online:</span>
                                                <strong class="text-dark">Rp{{ number_format($onlineAnalysis['price'], 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>HPP Total:</span>
                                                <span class="text-muted">Rp{{ number_format($selectedHppDetails['hpp_total'], 0, ',', '.') }}</span>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Margin Nominal:</span>
                                                <strong class="text-success">Rp{{ number_format($onlineAnalysis['margin_nominal'], 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Margin Persentase:</span>
                                                <strong class="text-primary">{{ number_format($onlineAnalysis['margin_percent'], 2, ',', '.') }}%</strong>
                                            </div>
                                            <div class="alert alert-{{ $onlineAnalysis['margin_percent'] >= $validatedTargetMargin ? 'success' : 'warning' }} py-1 px-2 mb-0 small text-center">
                                                <strong>Status:</strong> {{ $onlineAnalysis['status'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BAGIAN 3: TARGET MARGIN & HARGA REKOMENDASI --}}
                            <div class="card card-hero border mb-0 bg-light">
                                <div class="card-header py-2 bg-info text-white">
                                    <h6 class="mb-0 font-weight-bold text-white"><i class="fas fa-bullseye mr-2"></i>Analisis Target Margin & Simulasi Harga Jual</h6>
                                </div>
                                <div class="card-body py-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 mb-2">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold mb-1">Target Margin (%)</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" min="0" max="99" class="form-control text-center font-weight-bold" wire:model.live="targetMarginPercent">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold mb-1">Kelipatan Pembulatan</label>
                                                <select class="form-control form-control-sm" wire:model.live="roundingStep">
                                                    <option value="100">Rp100</option>
                                                    <option value="500">Rp500</option>
                                                    <option value="1000">Rp1.000</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="border rounded p-3 bg-white">
                                                <small class="text-muted d-block">Harga Jual Minimal (Teoritis):</small>
                                                <strong class="text-dark h6">Rp{{ number_format($theoreticalPrice, 2, ',', '.') }}</strong>

                                                <hr class="my-2">

                                                <small class="text-muted d-block">Harga Jual Disarankan (Pembulatan):</small>
                                                <strong class="text-success h4 mb-2 d-block">Rp{{ number_format($suggestedPrice, 0, ',', '.') }}</strong>

                                                <div class="btn-group w-100">
                                                    <button type="button" wire:click="applySuggestedPriceToProduct({{ $selectedProduct->id }}, 'offline', {{ $suggestedPrice }})" class="btn btn-sm btn-primary">
                                                        Set Harga Offline
                                                    </button>
                                                    <button type="button" wire:click="applySuggestedPriceToProduct({{ $selectedProduct->id }}, 'online', {{ $suggestedPrice }})" class="btn btn-sm btn-success">
                                                        Set Harga Online
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-secondary" wire:click="closeDetail">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>
