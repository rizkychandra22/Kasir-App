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
                         x-init="setTimeout(() => show = false, 3000)"
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
                                    <i class="fas fa-plus-circle mr-1"></i> Category
                                </a>
                                <a href="{{ route('kasir.product') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left mr-1"></i> Produk
                                </a>
                                <a href="" class="btn btn-success">
                                    <i class="fas fa-print mr-1"></i> Cetak
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            {{-- KOLOM KIRI: FORM INPUT --}}
                            <div class="col-md-4">
                                <div class="section-title mt-0">{{ $isEdit ? 'Edit Kategori' : 'Tambah Kategori Baru' }}</div>
                                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                                    <div class="form-group">
                                        <label>Nama Kategori</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.live="name" placeholder="Contoh: Cemilan Kering">
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Kode Kategori</label>
                                        <input type="text" 
                                            class="form-control @error('name_code') is-invalid @enderror" 
                                            wire:model="name_code" 
                                            readonly 
                                            placeholder="Otomatis dibuat oleh sistem">
                                        @error('name_code') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                        <small class="text-muted">*Kode dihasilkan otomatis oleh sistem.</small>
                                    </div>
                                    <div class="mt-4 justify-content-start d-flex">
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
                                <div class="section-title mt-0">Daftar Kategori</div>
                                <div class="table-responsive" wire:poll.5s>
                                    <table class="table table-bordered table-hover table-md">
                                        <thead class="thead-light text-center">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Kategori</th>
                                                <th>Kode</th>
                                                <th>Produk</th>
                                                <th>User</th>
                                                <th width="120">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($categories as $category)
                                                <tr wire:key="category-{{ $category->id }}">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $category->name }}</td>
                                                    <td class="text-center"><span class="badge badge-info">{{ $category->name_code }}</span></td>
                                                    <td class="text-center"><span class="badge badge-success">{{ $category->products_count }}</span></td>
                                                    <td class="text-center"><code class="font-weight-bold">{{ $category->user->name }}</code></td>
                                                    <td class="text-center">
                                                        <button wire:click="edit({{ $category->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button wire:click="delete({{ $category->id }})" 
                                                                wire:confirm="Hapus kategori {{ $category->name }}?"
                                                                class="btn btn-sm btn-outline-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Belum ada data.</td>
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
</div>