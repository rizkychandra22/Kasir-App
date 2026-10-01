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
                {{-- TOP SUMMARY CARDS --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card card-statistic-1 border shadow-sm mb-0">
                            <div class="card-icon bg-warning text-white">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Biaya Operasional Aktif</h4>
                                </div>
                                <div class="card-body">
                                    Rp{{ number_format($totalActiveNominal, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-statistic-1 border shadow-sm mb-0">
                            <div class="card-icon bg-success text-white">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Target Penjualan Bulanan</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($targetMonthly, 0, ',', '.') }} cup
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-statistic-1 border shadow-sm mb-0">
                            <div class="card-icon bg-info text-white">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Biaya Overhead per Cup</h4>
                                </div>
                                <div class="card-body">
                                    Rp{{ number_format($overheadCostPerCup, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>{{ $content }}</h4>
                        <div class="card-header-action">
                            <div class="btn-group">
                                <a href="{{ route('kasir.target-penjualan') }}" class="btn btn-info">
                                    <i class="fas fa-bullseye mr-1"></i> Target Penjualan
                                </a>
                                <a href="{{ route('kasir.labor') }}" class="btn btn-primary">
                                    <i class="fas fa-users mr-1"></i> Data Tenaga Kerja
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- FORM INPUT OVERHEAD --}}
                            <div class="col-md-4">
                                <div class="section-title mt-0">{{ $isEdit ? 'Edit Biaya Operasional' : 'Tambah Biaya Baru' }}</div>
                                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                                    <div class="form-group mb-2">
                                        <label>Nama Biaya Operasional</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Contoh: Listrik & Air, Sewa Tempat, WiFi">
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>Kategori Biaya</label>
                                        <select class="form-control @error('category') is-invalid @enderror" wire:model="category">
                                            <option value="Fixed Cost">Fixed Cost (Biaya Tetap)</option>
                                            <option value="Variable Cost">Variable Cost (Biaya Variabel)</option>
                                            <option value="Operational">Operational (Operasional Umum)</option>
                                        </select>
                                        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>Nominal Biaya per Bulan (Rp)</label>
                                        <input type="number" step="any" class="form-control @error('nominal_monthly') is-invalid @enderror" wire:model="nominal_monthly" placeholder="1500000">
                                        @error('nominal_monthly') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>Status</label>
                                        <select class="form-control @error('status') is-invalid @enderror" wire:model="status">
                                            <option value="active">Aktif</option>
                                            <option value="inactive">Non-Aktif</option>
                                        </select>
                                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>Catatan (Opsional)</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" wire:model="notes" rows="2" placeholder="Catatan tagihan, jatuh tempo, dll..."></textarea>
                                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

                            {{-- TABEL LIST BIAYA OPERASIONAL --}}
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="section-title mt-0 mb-0">Daftar Biaya Operasional</div>
                                    <div class="w-50">
                                        <input type="text" class="form-control" wire:model.live="search" placeholder="Cari nama biaya / kategori...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th class="text-center" style="width: 5%">#</th>
                                                <th>Nama Biaya</th>
                                                <th class="text-center">Kategori</th>
                                                <th class="text-right">Nominal / Bulan</th>
                                                <th class="text-center">Status</th>
                                                <th>Catatan</th>
                                                <th class="text-center" style="width: 15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($overheads as $index => $item)
                                                <tr class="{{ $item->status === 'inactive' ? 'table-secondary opacity-75' : '' }}">
                                                    <td class="text-center">{{ $overheads->firstItem() + $index }}</td>
                                                    <td class="font-weight-bold">{{ $item->name }}</td>
                                                    <td class="text-center"><span class="badge badge-light border">{{ $item->category }}</span></td>
                                                    <td class="text-right font-weight-bold text-warning">Rp{{ number_format($item->nominal_monthly, 0, ',', '.') }}</td>
                                                    <td class="text-center">
                                                        <button type="button" wire:click="toggleStatus({{ $item->id }})" class="btn btn-sm {{ $item->status === 'active' ? 'btn-success' : 'btn-secondary' }}">
                                                            {{ $item->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                                        </button>
                                                    </td>
                                                    <td class="small">{{ $item->notes ?? '-' }}</td>
                                                    <td class="text-center">
                                                        <div class="btn-group">
                                                            <button type="button" wire:click="edit({{ $item->id }})" class="btn btn-sm btn-info" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" onclick="confirm('Yakin ingin menghapus data ini?') || event.stopImmediatePropagation()" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-danger" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data biaya operasional.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    {{ $overheads->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
