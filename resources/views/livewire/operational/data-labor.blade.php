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
                            <div class="card-icon bg-primary text-white">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Gaji Karyawan Aktif</h4>
                                </div>
                                <div class="card-body">
                                    Rp{{ number_format($totalActiveSalary, 0, ',', '.') }}
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
                                    <h4>Biaya Labor per Cup</h4>
                                </div>
                                <div class="card-body">
                                    Rp{{ number_format($laborCostPerCup, 2, ',', '.') }}
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
                                <a href="{{ route('kasir.overhead') }}" class="btn btn-warning">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Biaya Operasional
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- FORM INPUT LABORS --}}
                            <div class="col-md-4">
                                <div class="section-title mt-0">{{ $isEdit ? 'Edit Tenaga Kerja' : 'Tambah Tenaga Kerja Baru' }}</div>
                                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                                    <div class="form-group mb-2">
                                        <label>Nama Karyawan / Posisi</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Contoh: Barista, Kasir, Helper">
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>Gaji / Upah per Bulan (Rp)</label>
                                        <input type="number" step="any" class="form-control @error('monthly_salary') is-invalid @enderror" wire:model="monthly_salary" placeholder="3500000">
                                        @error('monthly_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                        <textarea class="form-control @error('notes') is-invalid @enderror" wire:model="notes" rows="2" placeholder="Catatan jam kerja, shift, dll..."></textarea>
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

                            {{-- TABEL LIST TENAGA KERJA --}}
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="section-title mt-0 mb-0">Daftar Tenaga Kerja</div>
                                    <div class="w-50">
                                        <input type="text" class="form-control" wire:model.live="search" placeholder="Cari nama karyawan / posisi...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th class="text-center" style="width: 5%">#</th>
                                                <th>Nama / Posisi</th>
                                                <th class="text-right">Gaji / Bulan</th>
                                                <th class="text-center">Status</th>
                                                <th>Catatan</th>
                                                <th class="text-center" style="width: 15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($labors as $index => $item)
                                                <tr class="{{ $item->status === 'inactive' ? 'table-secondary opacity-75' : '' }}">
                                                    <td class="text-center">{{ $labors->firstItem() + $index }}</td>
                                                    <td class="font-weight-bold">{{ $item->name }}</td>
                                                    <td class="text-right font-weight-bold text-success">Rp{{ number_format($item->monthly_salary, 0, ',', '.') }}</td>
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
                                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data tenaga kerja.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    {{ $labors->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
