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
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalProduk" wire:click="resetInput">
                                    <i class="fas fa-plus-circle mr-1"></i> Produk
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
                                        <th width="50">#</th>
                                        <th>User</th>
                                        <th>Kategori</th>
                                        <th>Produk</th>
                                        <th>Kode</th>
                                        <th>Deskripsi</th>
                                        <th width="120">Harga</th>
                                        <th>Stok</th>
                                        <th width="115">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        <tr wire:key="product-{{ $product->id }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td><code class="font-weight-bold">{{ $product->user->name }}</code></td>
                                            <td>{{ $product->category->name }}</td>
                                            <td>{{ $product->name_prd }}</td>
                                            <td><span class="badge badge-warning">{{ $product->code_prd }}</span></td>
                                            <td>{{ trim($product->description_prd ?? '') !== '' ? $product->description_prd : '—' }}</td>
                                            <td class="font-weight-bold">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $product->stock <= 10 ? 'info' : 'primary' }}">
                                                    {{ $product->stock }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning mt-1 mb-1 mr-1" wire:click="edit({{ $product->id }})" data-toggle="modal" data-target="#modalProduk">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger mt-1 mb-1" wire:click="delete({{ $product->id }})" wire:confirm="Hapus produk {{ $product->name_prd }}?">
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
    <div wire:ignore.self class="modal fade" id="modalProduk" tabindex="-1" role="dialog" aria-labelledby="modalProdukLabel" aria-hidden="true"
        x-data="{ isEdit: @js($productId) }"
        @keydown.enter.prevent="isEdit ? $wire.update() : $wire.store()"
        @keydown.escape.prevent="$('#modalProduk').modal('hide')">
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
                                <div class="form-group">
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
                                <div class="form-group">
                                    <label>Kode Produk</label>
                                    <input type="text" class="form-control @error('code_prd') is-invalid @enderror" 
                                        wire:model="code_prd" readonly 
                                        {{ $productId ? 'disabled' : '' }} 
                                        placeholder="Otomatis: M9RMK3S">
                                    @if($productId)
                                        <small class="text-danger">*Product code cannot be changed while in edit mode.</small>
                                    @else
                                        <small class="text-danger">*Generated Automatically your code product.</small>
                                    @endif
                                    @error('code_prd') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" class="form-control @error('name_prd') is-invalid @enderror" wire:model.live="name_prd" placeholder="Masukkan nama produk">
                            @error('name_prd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label>Deskripsi (Opsional)</label>
                            <textarea class="form-control" wire:model="description_prd" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Harga Jual</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">Rp</div>
                                        </div>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" wire:model="price">
                                    </div>
                                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Stok</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror" wire:model="stock">
                                    @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
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

@push('scripts')
    <script>
        window.addEventListener('close-modal', event => {
            $('#modalProduk').modal('hide');
        });
    </script>
@endpush
