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
                                <a href="{{ route('kasir.category') }}" class="btn btn-danger">
                                    <i class="fas fa-list mr-1"></i> Kategori
                                </a>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalProduk" wire:click="resetInput">
                                    <i class="fas fa-plus-circle mr-1"></i> Produk Baru
                                </button>
                                <a href="{{ route('kasir.product.export') }}" class="btn btn-success" rel="noopener noreferrer">
                                    <i class="fas fa-file-export mr-1"></i> Export
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive" wire:poll.5s>
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light text-center">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>User</th>
                                        <th>Kategori</th>
                                        <th>Produk</th>
                                        <th>Kode</th>
                                        <th>Tipe Penjualan</th>
                                        <th>Harga Jual</th>
                                        <th>Resep / Komposisi Bahan</th>
                                        <th width="115">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        <tr wire:key="product-{{ $product->id }}">
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td><code class="font-weight-bold">{{ $product->user->name ?? '-' }}</code></td>
                                            <td>{{ $product->category->name ?? '-' }}</td>
                                            <td class="font-weight-bold">{{ $product->name_prd }}</td>
                                            <td class="text-center"><span class="badge badge-warning">{{ $product->code_prd }}</span></td>
                                            <td class="text-center">
                                                @if($product->sales_type === 'online')
                                                    <span class="badge badge-success"><i class="fas fa-globe mr-1"></i> Online</span>
                                                @elseif($product->sales_type === 'offline')
                                                    <span class="badge badge-secondary"><i class="fas fa-store mr-1"></i> Offline</span>
                                                @else
                                                    <span class="badge badge-info"><i class="fas fa-shopping-bag mr-1"></i> Online & Offline</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->sales_type === 'online' || $product->sales_type === 'all')
                                                    <div><small class="text-muted">Online:</small> <span class="font-weight-bold text-success">Rp{{ number_format($product->price_online ?? $product->price, 0, ',', '.') }}</span></div>
                                                @endif
                                                @if($product->sales_type === 'offline' || $product->sales_type === 'all')
                                                    <div><small class="text-muted">Offline:</small> <span class="font-weight-bold text-primary">Rp{{ number_format($product->price_offline ?? $product->price, 0, ',', '.') }}</span></div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->bahans->count() > 0)
                                                    @php
                                                        $costInfo = $product->getRecipeCostDetails();
                                                    @endphp
                                                    <div class="table-responsive mb-1">
                                                        <table class="table table-sm table-bordered bg-white mb-1 small text-dark" style="min-width: 320px;">
                                                            <thead class="bg-light text-center">
                                                                <tr>
                                                                    <th>Bahan</th>
                                                                    <th>Qty</th>
                                                                    <th>Unit</th>
                                                                    <th>Harga</th>
                                                                    <th>Biaya</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($costInfo['details'] as $item)
                                                                    <tr>
                                                                        <td class="font-weight-bold">{{ $item['name_bahan'] }}</td>
                                                                        <td class="text-center">{{ number_format($item['quantity'], 2, ',', '.') }}</td>
                                                                        <td class="text-center"><code>{{ $item['unit'] }}</code></td>
                                                                        <td class="text-right text-muted small">
                                                                            @if($item['has_valid_price'])
                                                                                Rp{{ number_format($item['cost_per_unit'], 2, ',', '.') }}/{{ $item['unit'] }}
                                                                            @else
                                                                                <span class="badge badge-warning text-dark small">Harga belum tersedia</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-right font-weight-bold text-success">
                                                                            @if($item['has_valid_price'])
                                                                                Rp{{ number_format($item['item_cost'], 0, ',', '.') }}
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="bg-light font-weight-bold">
                                                                    <td colspan="4" class="text-right small text-uppercase">TOTAL BIAYA BAHAN:</td>
                                                                    <td class="text-right text-primary font-weight-bold">Rp{{ number_format($costInfo['total_cost'], 0, ',', '.') }}</td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                @endif
                                                <small class="text-muted d-block">{{ trim($product->description_prd ?? '') !== '' ? $product->description_prd : '—' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-warning mt-1 mb-1 mr-1" wire:click="edit({{ $product->id }})" data-toggle="modal" data-target="#modalProduk" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger mt-1 mb-1" wire:click="delete({{ $product->id }})" wire:confirm="Hapus produk {{ $product->name_prd }}?" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Tidak ada data produk</td>
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

    {{-- MODAL TAMBAH & EDIT PRODUK --}}
    <div wire:ignore.self class="modal fade" id="modalProduk" tabindex="-1" role="dialog" aria-labelledby="modalProdukLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">                                                                                                                                                                         
                <div class="modal-header">
                    <h5 class="modal-title">{{ $productId ? 'Edit Produk' : 'Tambah Produk Baru' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="{{ $productId ? 'update' : 'store' }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label>Kategori</label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" wire:model.live="category_id">
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->name_code }})</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label>Kode Produk</label>
                                    <input type="text" class="form-control @error('code_prd') is-invalid @enderror" 
                                        wire:model="code_prd" readonly 
                                        {{ $productId ? 'disabled' : '' }} 
                                        placeholder="Otomatis dari kategori & nama">
                                    <small class="text-muted">*Generated otomatis oleh sistem.</small>
                                    @error('code_prd') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label>Nama Produk</label>
                            <input type="text" class="form-control @error('name_prd') is-invalid @enderror" wire:model.live="name_prd" placeholder="Masukkan nama produk (misal: Coffee Latte)">
                            @error('name_prd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- TIPE PENJUALAN & HARGA --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <label class="d-block font-weight-bold">Tipe Penjualan</label>
                                    <div class="form-check form-check-inline mr-4">
                                        <input class="form-check-input" type="radio" wire:model.live="sales_type" id="st_all" value="all">
                                        <label class="form-check-label font-weight-bold text-info" for="st_all">
                                            <i class="fas fa-shopping-bag mr-1"></i> Online & Offline
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline mr-4">
                                        <input class="form-check-input" type="radio" wire:model.live="sales_type" id="st_online" value="online">
                                        <label class="form-check-label font-weight-bold text-success" for="st_online">
                                            <i class="fas fa-globe mr-1"></i> Online Sahaja
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" wire:model.live="sales_type" id="st_offline" value="offline">
                                        <label class="form-check-label font-weight-bold text-secondary" for="st_offline">
                                            <i class="fas fa-store mr-1"></i> Offline Sahaja
                                        </label>
                                    </div>
                                    @error('sales_type') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @if($sales_type === 'online' || $sales_type === 'all')
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>Harga Jual Online</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">Rp</div>
                                            </div>
                                            <input type="number" class="form-control @error('price_online') is-invalid @enderror" wire:model="price_online" placeholder="Harga online">
                                        </div>
                                        @error('price_online') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            @endif

                            @if($sales_type === 'offline' || $sales_type === 'all')
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>Harga Jual Offline</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">Rp</div>
                                            </div>
                                            <input type="number" class="form-control @error('price_offline') is-invalid @enderror" wire:model="price_offline" placeholder="Harga offline">
                                        </div>
                                        @error('price_offline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- SELEKSI & JUMLAH KOMPOSISI RESEP BAHAN --}}
                        <div class="form-group border rounded p-3 bg-light mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="font-weight-bold mb-0"><i class="fas fa-cubes text-warning mr-1"></i> Resep / Komposisi Bahan Produk</label>
                                <button type="button" class="btn btn-sm btn-info" wire:click="addCompositionRow">
                                    <i class="fas fa-plus mr-1"></i> Tambah Bahan Resep
                                </button>
                            </div>

                            @if(count($compositions) > 0)
                                @php $modalTotalCost = 0; @endphp
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white mb-2">
                                        <thead class="thead-light text-center">
                                            <tr>
                                                <th>Pilih Master Bahan</th>
                                                <th width="100">Jumlah (Qty)</th>
                                                <th width="110">Satuan Resep</th>
                                                <th width="120">Harga Satuan</th>
                                                <th width="120">Biaya Bahan</th>
                                                <th width="45">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($compositions as $index => $comp)
                                                @php
                                                    $selectedBahanModel = !empty($comp['bahan_id']) ? $activeBahans->firstWhere('id', $comp['bahan_id']) : null;
                                                    $costPerRecipeUnit = 0;
                                                    $rowCost = 0;
                                                    $hasValidPrice = false;

                                                    if ($selectedBahanModel) {
                                                        $hasValidPrice = $selectedBahanModel->hasValidPrice();
                                                        if ($hasValidPrice) {
                                                            $baseCost = $selectedBahanModel->cost_per_base_unit;
                                                            $useUnit = $comp['unit'] ?? $selectedBahanModel->base_unit;
                                                            $qtyInBase = \App\Services\UnitConversionService::convertToBaseUnit((float)($comp['quantity'] ?? 0), $useUnit)['amount'];
                                                            $rowCost = $qtyInBase * $baseCost;
                                                            $modalTotalCost += $rowCost;

                                                            $recipeUnitConversion = \App\Services\UnitConversionService::convertToBaseUnit(1, $useUnit)['amount'];
                                                            $costPerRecipeUnit = $recipeUnitConversion * $baseCost;
                                                        }
                                                    }
                                                @endphp
                                                <tr wire:key="comp-row-{{ $index }}">
                                                    <td>
                                                        <select class="form-control form-control-sm @error('compositions.'.$index.'.bahan_id') is-invalid @enderror" 
                                                                wire:model.live="compositions.{{ $index }}.bahan_id">
                                                            <option value="">-- Pilih Bahan Baku --</option>
                                                            @foreach($activeBahans as $b)
                                                                <option value="{{ $b->id }}">{{ $b->name_bahan }} (Stok: {{ number_format((float)$b->stock, 2, ',', '.') }} {{ $b->base_unit ?? $b->unit }})</option>
                                                            @endforeach
                                                        </select>
                                                        @error('compositions.'.$index.'.bahan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" class="form-control form-control-sm @error('compositions.'.$index.'.quantity') is-invalid @enderror" 
                                                               wire:model.live="compositions.{{ $index }}.quantity" placeholder="18">
                                                        @error('compositions.'.$index.'.quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </td>
                                                    <td>
                                                        <select class="form-control form-control-sm @error('compositions.'.$index.'.unit') is-invalid @enderror"
                                                                wire:model.live="compositions.{{ $index }}.unit">
                                                            <option value="gram">gram (g)</option>
                                                            <option value="kg">kg (kilogram)</option>
                                                            <option value="ml">ml (milliliter)</option>
                                                            <option value="liter">liter (l)</option>
                                                            <option value="pcs">pcs</option>
                                                            <option value="unit">unit</option>
                                                            <option value="cup">cup</option>
                                                            <option value="botol">botol</option>
                                                            <option value="sachet">sachet</option>
                                                            <option value="sendok">sendok</option>
                                                        </select>
                                                        @error('compositions.'.$index.'.unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </td>
                                                    <td class="text-right align-middle font-weight-bold">
                                                        @if($selectedBahanModel)
                                                            @if($hasValidPrice)
                                                                <small class="text-muted">Rp{{ number_format($costPerRecipeUnit, 2, ',', '.') }}/{{ $comp['unit'] ?? $selectedBahanModel->base_unit }}</small>
                                                            @else
                                                                <span class="badge badge-warning text-dark small">Harga belum tersedia</span>
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="text-right align-middle font-weight-bold text-success">
                                                        @if($selectedBahanModel)
                                                            @if($hasValidPrice)
                                                                Rp{{ number_format($rowCost, 0, ',', '.') }}
                                                            @else
                                                                -
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeCompositionRow({{ $index }})">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="4" class="text-right small text-uppercase">TOTAL BIAYA BAHAN:</td>
                                                <td class="text-right text-primary font-weight-bold">Rp{{ number_format($modalTotalCost, 0, ',', '.') }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-0 text-center py-2">Belum ada bahan ditambahkan ke resep produk ini. Klik <strong>[+ Tambah Bahan Resep]</strong> untuk memilih dari Master Bahan Baku.</p>
                            @endif
                        </div>

                        <div class="form-group mb-0">
                            <label>Deskripsi Produk</label>
                            <textarea class="form-control" wire:model="description_prd" rows="2" placeholder="Deskripsi produk atau komposisi bahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="fas fa-save mr-1"></i> {{ $productId ? 'Update Produk' : 'Simpan Produk' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
