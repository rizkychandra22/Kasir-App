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
                                    <i class="fas fa-arrow-left mr-1"></i> Data Produk
                                </a>
                                <a href="{{ route('kasir.category') }}" class="btn btn-info">
                                    <i class="fas fa-list mr-1"></i> Kategori
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- KOLOM KIRI: FORM INPUT --}}
                            <div class="col-md-4">
                                <div class="section-title mt-0">{{ $isEdit ? 'Edit Master Bahan' : 'Tambah Bahan Baru' }}</div>
                                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                                    <div class="form-group mb-2">
                                        <label>Nama Bahan</label>
                                        <input type="text" class="form-control @error('name_bahan') is-invalid @enderror" wire:model="name_bahan" placeholder="Contoh: Kopi Arabika, Susu, Cup">
                                        @error('name_bahan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    @if(!$isEdit)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label>Satuan Pembelian</label>
                                                    <select class="form-control @error('purchase_unit') is-invalid @enderror" wire:model.live="purchase_unit">
                                                        <optgroup label="Kelompok Berat">
                                                            <option value="kg">kg (kilogram)</option>
                                                            <option value="gram">gram (g)</option>
                                                        </optgroup>
                                                        <optgroup label="Kelompok Volume">
                                                            <option value="liter">liter (l)</option>
                                                            <option value="ml">ml (milliliter)</option>
                                                        </optgroup>
                                                        <optgroup label="Kelompok Jumlah">
                                                            <option value="pcs">pcs</option>
                                                            <option value="unit">unit</option>
                                                            <option value="cup">cup</option>
                                                            <option value="botol">botol</option>
                                                            <option value="sachet">sachet</option>
                                                            <option value="sendok">sendok</option>
                                                            <option value="buah">buah</option>
                                                        </optgroup>
                                                    </select>
                                                    @error('purchase_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label>Jumlah Pembelian</label>
                                                    <input type="number" step="any" class="form-control @error('purchase_qty') is-invalid @enderror" wire:model.live="purchase_qty" placeholder="1">
                                                    @error('purchase_qty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- INFO KONVERSI SATUAN DASAR --}}
                                        <div class="alert alert-light border py-2 px-3 mb-2 small text-dark">
                                            <i class="fas fa-calculator text-primary mr-1"></i> Satuan Dasar (Base Unit): <strong>{{ $base_unit }}</strong><br>
                                            Stok tersimpan: <strong class="text-success">{{ number_format((float)$stock, 3, ',', '.') }} {{ $base_unit }}</strong>
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label>Satuan Dasar (Base Unit)</label>
                                                    <input type="text" class="form-control" wire:model="base_unit" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label>Stok Tersimpan</label>
                                                    <input type="number" step="any" class="form-control @error('stock') is-invalid @enderror" wire:model="stock">
                                                    @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label>Harga Pembelian (Rp)</label>
                                                <input type="number" class="form-control @error('price') is-invalid @enderror" wire:model="price" placeholder="150000">
                                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label>Status</label>
                                                <select class="form-control @error('status') is-invalid @enderror" wire:model="status">
                                                    <option value="active">Aktif</option>
                                                    <option value="inactive">Nonaktif</option>
                                                </select>
                                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>Deskripsi / Catatan Bahan</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" wire:model="description" rows="2" placeholder="Spesifikasi atau catatan bahan..."></textarea>
                                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mt-3 justify-content-start d-flex">
                                        @if($isEdit)
                                            <button type="button" wire:click="resetInput" class="btn btn-danger shadow-sm mr-2">Batal</button>
                                        @endif
                                        <button type="submit" class="btn btn-primary shadow-sm">
                                            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Update' : 'Simpan' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- KOLOM KANAN: TABEL DATA --}}
                            <div class="col-md-8 border-left">
                                <div class="section-title mt-0">Daftar Master Bahan</div>
                                <div class="table-responsive" wire:poll.5s>
                                    <table class="table table-bordered table-hover table-md">
                                        <thead class="thead-light text-center">
                                            <tr>
                                                <th width="30">#</th>
                                                <th>Nama Bahan</th>
                                                <th>Harga Beli</th>
                                                <th>Pembelian</th>
                                                <th>Base Unit</th>
                                                <th>Harga / Base Unit</th>
                                                <th>Stok Tersedia</th>
                                                <th>Status</th>
                                                <th width="140">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($bahans as $bahan)
                                                @php
                                                    $hasPrice = $bahan->hasValidPrice();
                                                    $costBase = $bahan->cost_per_base_unit;
                                                @endphp
                                                <tr wire:key="bahan-{{ $bahan->id }}" class="{{ $bahan->status === 'inactive' ? 'bg-light text-muted' : '' }}">
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td class="font-weight-bold">
                                                        {{ $bahan->name_bahan }}
                                                        @if(!empty($bahan->description))
                                                            <small class="d-block text-muted font-weight-normal">{{ $bahan->description }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-right font-weight-bold text-dark">
                                                        @if($hasPrice)
                                                            Rp{{ number_format($bahan->price, 0, ',', '.') }}
                                                        @else
                                                            <span class="badge badge-warning text-dark">Harga belum tersedia</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-light border">{{ (float)($bahan->purchase_qty ?? 1) }} {{ $bahan->purchase_unit ?? $bahan->unit }}</span>
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        <code>{{ $bahan->base_unit ?? $bahan->unit }}</code>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-primary">
                                                        @if($hasPrice)
                                                            Rp{{ number_format($costBase, 2, ',', '.') }}/{{ $bahan->base_unit ?? $bahan->unit }}
                                                        @else
                                                            <span class="badge badge-warning text-dark small">Harga belum tersedia</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        <span class="badge badge-{{ (float)$bahan->stock <= 50 ? 'danger' : 'success' }}">
                                                            {{ number_format((float)$bahan->stock, 2, ',', '.') }} {{ $bahan->base_unit ?? $bahan->unit }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($bahan->status === 'active')
                                                            <span class="badge badge-success" wire:click="toggleStatus({{ $bahan->id }})" style="cursor: pointer;" title="Klik untuk nonaktifkan">
                                                                <i class="fas fa-check-circle mr-1"></i> Aktif
                                                            </span>
                                                        @else
                                                            <span class="badge badge-danger" wire:click="toggleStatus({{ $bahan->id }})" style="cursor: pointer;" title="Klik untuk aktifkan">
                                                                <i class="fas fa-times-circle mr-1"></i> Nonaktif
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group">
                                                            <button wire:click="viewDetail({{ $bahan->id }})" data-toggle="modal" data-target="#modalDetailBahan" class="btn btn-sm btn-outline-primary" title="Detail Bahan">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button wire:click="edit({{ $bahan->id }})" class="btn btn-sm btn-outline-warning" title="Edit Data">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button wire:click="openAdjustStock({{ $bahan->id }})" data-toggle="modal" data-target="#modalAdjustStock" class="btn btn-sm btn-outline-success" title="Tambah Stok">
                                                                <i class="fas fa-boxes"></i>
                                                            </button>
                                                            <button wire:click="viewHistory({{ $bahan->id }})" data-toggle="modal" data-target="#modalHistoryStock" class="btn btn-sm btn-outline-info" title="Riwayat Pergerakan Stok">
                                                                <i class="fas fa-history"></i>
                                                            </button>
                                                            <button wire:click="delete({{ $bahan->id }})" 
                                                                    wire:confirm="Hapus atau nonaktifkan bahan {{ $bahan->name_bahan }}?"
                                                                    class="btn btn-sm btn-outline-danger" title="Hapus / Nonaktifkan">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted">Belum ada data bahan.</td>
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

    {{-- MODAL PENYESUAIAN STOK --}}
    <div wire:ignore.self class="modal fade" id="modalAdjustStock" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-boxes mr-2"></i> Tambah / Penyesuaian Stok: {{ $adjustBahanName }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveAdjustStock">
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label>Jenis Perubahan Stok</label>
                            <select class="form-control" wire:model="adjustType">
                                <option value="in">Stok Masuk (+)</option>
                                <option value="out">Stok Keluar (-)</option>
                                <option value="adjustment">Set Ulang Stok (Set Exact Base Unit)</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label>Jumlah</label>
                                    <input type="number" step="any" class="form-control" wire:model="adjustQty" placeholder="Jumlah">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label>Satuan</label>
                                    <select class="form-control" wire:model="adjustUnit">
                                        <option value="kg">kg (kilogram)</option>
                                        <option value="gram">gram (g)</option>
                                        <option value="liter">liter (l)</option>
                                        <option value="ml">ml (milliliter)</option>
                                        <option value="pcs">pcs</option>
                                        <option value="unit">unit</option>
                                        <option value="cup">cup</option>
                                        <option value="botol">botol</option>
                                        <option value="sachet">sachet</option>
                                        <option value="sendok">sendok</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label>Catatan / Keterangan</label>
                            <input type="text" class="form-control" wire:model="adjustNotes" placeholder="Contoh: Pembelian ulang 2 kg, barang rusak, dll.">
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Simpan Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL RIWAYAT STOK MOVEMENT --}}
    <div wire:ignore.self class="modal fade" id="modalHistoryStock" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-history mr-2"></i> Riwayat Pergerakan Stok: {{ $selectedBahanForHistory->name_bahan ?? '' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @if($selectedBahanForHistory && $selectedBahanForHistory->stockMovements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Waktu</th>
                                        <th>Jenis</th>
                                        <th>Jumlah</th>
                                        <th>Stok Sebelum</th>
                                        <th>Stok Sesudah</th>
                                        <th>Referensi</th>
                                        <th>Keterangan</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedBahanForHistory->stockMovements as $m)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-center">
                                                @if($m->type === 'in')
                                                    <span class="badge badge-success">MASUK (+)</span>
                                                @elseif($m->type === 'out')
                                                    <span class="badge badge-danger">KELUAR (-)</span>
                                                @else
                                                    <span class="badge badge-warning">SET (ADJ)</span>
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold">{{ number_format((float)$m->qty, 2, ',', '.') }} {{ $selectedBahanForHistory->base_unit ?? $selectedBahanForHistory->unit }}</td>
                                            <td class="text-center">{{ number_format((float)$m->stock_before, 2, ',', '.') }}</td>
                                            <td class="text-center font-weight-bold">{{ number_format((float)$m->stock_after, 2, ',', '.') }}</td>
                                            <td class="text-center"><span class="badge badge-light border">{{ $m->reference ?? '-' }}</span></td>
                                            <td>{{ $m->notes ?? '-' }}</td>
                                            <td class="text-center"><code class="font-weight-bold">{{ $m->user->name ?? 'System' }}</code></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle d-block mb-2" style="font-size: 24px;"></i>
                            Belum ada riwayat pergerakan stok untuk bahan ini.
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL BAHAN --}}
    <div wire:ignore.self class="modal fade" id="modalDetailBahan" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i> Detail Master Bahan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @if($selectedBahanForDetail)
                        @php
                            $convertedDetail = \App\Services\UnitConversionService::convertToBaseUnit(
                                (float)($selectedBahanForDetail->purchase_qty ?? 1), 
                                $selectedBahanForDetail->purchase_unit ?? $selectedBahanForDetail->unit
                            );
                            $baseQtyDetail = $convertedDetail['amount'];
                            $hasPriceDetail = $selectedBahanForDetail->hasValidPrice();
                            $costBaseDetail = $selectedBahanForDetail->cost_per_base_unit;
                        @endphp
                        <table class="table table-striped table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th width="40%">Nama Bahan</th>
                                    <td class="font-weight-bold">{{ $selectedBahanForDetail->name_bahan }}</td>
                                </tr>
                                <tr>
                                    <th>Harga Pembelian</th>
                                    <td class="font-weight-bold text-dark">
                                        @if($hasPriceDetail)
                                            Rp{{ number_format($selectedBahanForDetail->price, 0, ',', '.') }}
                                        @else
                                            <span class="badge badge-warning text-dark">Harga belum tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jumlah Pembelian</th>
                                    <td>{{ (float)($selectedBahanForDetail->purchase_qty ?? 1) }} {{ $selectedBahanForDetail->purchase_unit ?? $selectedBahanForDetail->unit }}</td>
                                </tr>
                                <tr>
                                    <th>Base Unit</th>
                                    <td><code>{{ $selectedBahanForDetail->base_unit ?? $selectedBahanForDetail->unit }}</code></td>
                                </tr>
                                <tr>
                                    <th>Konversi Base Unit</th>
                                    <td>{{ number_format($baseQtyDetail, 2, ',', '.') }} {{ $selectedBahanForDetail->base_unit ?? $selectedBahanForDetail->unit }}</td>
                                </tr>
                                <tr>
                                    <th>Harga per {{ $selectedBahanForDetail->base_unit ?? $selectedBahanForDetail->unit }}</th>
                                    <td class="font-weight-bold text-primary">
                                        @if($hasPriceDetail)
                                            Rp{{ number_format($costBaseDetail, 2, ',', '.') }}/{{ $selectedBahanForDetail->base_unit ?? $selectedBahanForDetail->unit }}
                                        @else
                                            <span class="badge badge-warning text-dark">Harga belum tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Stok Saat Ini</th>
                                    <td class="font-weight-bold text-success">
                                        {{ number_format((float)$selectedBahanForDetail->stock, 2, ',', '.') }} {{ $selectedBahanForDetail->base_unit ?? $selectedBahanForDetail->unit }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $selectedBahanForDetail->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($selectedBahanForDetail->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @if(!empty($selectedBahanForDetail->description))
                                    <tr>
                                        <th>Catatan</th>
                                        <td>{{ $selectedBahanForDetail->description }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-3 text-muted">Memuat data...</div>
                    @endif
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
