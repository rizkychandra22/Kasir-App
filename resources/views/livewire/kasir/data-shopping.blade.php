@push('scripts')
    <style>
        .dash { margin-top: 20px; margin-bottom: 20px; border-top: 1px dashed #6d6a6a; }
    </style>
    <script>
        window.addEventListener('close-modal', event => {
            $('#modalTambahPenjualan').modal('hide');
        });

        function printStruk() {
            var printContents = document.getElementById('printArea').innerHTML;
            var printWindow = window.open('', '', 'height=1000,width=1000');

            printWindow.document.write('<html><head><title>Cetak Struk</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: "Courier New", Courier, monospace; padding: 20px; width: 300px; }'); 
            // printWindow.document.write('.badge-info { border: 1px solid #000; color: #000; background: none; }');
            // printWindow.document.write('.text-danger { color: #000 !important; }');
            printWindow.document.write('.table td, .table th { padding: 2px; font-size: 12px; }');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(printContents);
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            
            setTimeout(function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }, 500);
        }
    </script>
@endpush

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

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ $content }}</h4>
                        <div class="card-header-action">
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalTambahPenjualan" wire:click="resetInput">
                                    <i class="fas fa-plus-circle mr-1"></i> Transaksi
                                </button>
                                <a href="" class="btn btn-success">
                                    <i class="fas fa-print mr-1"></i> Laporan
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive" wire:poll.5s>
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>No. Invoice</th>
                                        <th>Tanggal</th>
                                        <th>Kasir</th>
                                        <th>Total</th>
                                        <th>Bayar</th>
                                        <th>Kembalian</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($shoppings as $item)
                                        <tr wire:key="shopping-{{ $item->id }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td><span class="badge badge-primary">{{ $item->invoice }}</span></td>
                                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                            <td><code class="font-weight-bold">{{ $item->user->name }}</code></td>
                                            <td class="font-weight-bold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                                            <td class="font-weight-bold">Rp{{ number_format($item->pay, 0, ',', '.') }}</td>
                                            <td class="font-weight-bold">Rp{{ number_format($item->change, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-info" 
                                                        title="Detail" 
                                                        wire:click="viewDetail({{ $item->id }})" 
                                                        data-toggle="modal" 
                                                        data-target="#modalDetail">
                                                        <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Belum ada transaksi.</td>
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

    {{-- MODAL TAMBAH PENJUALAN --}}
    <div wire:ignore.self class="modal fade" id="modalTambahPenjualan" tabindex="-1" role="dialog" aria-hidden="true"
        x-data
        @keydown.window.enter.prevent="$wire.store()"
        @keydown.window.escape.prevent="$('#modalTambahPenjualan').modal('hide')">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document"> 
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-cash-register mr-2"></i> POS System (Sistem Kasir)</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="section-title mt-0">Cari Produk</div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" class="form-control" wire:model.live="search_prd" placeholder="Nama atau kode produk...">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fas fa-search"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="list-group shadow-sm border rounded" style="max-height: 400px; overflow-y: auto;">
                                @forelse($products as $p)
                                    <button type="button" wire:click="addToCart({{ $p->id }})" 
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-left-0 border-right-0">
                                        <div>
                                            <div class="font-weight-bold text-primary">{{ $p->name_prd }}</div>
                                            <small class="text-dark">{{ $p->code_prd }} | R:
                                                <span class="{{ $p->stock <= 10 ? 'text-danger font-weight-bold' : '' }}">{{ $p->stock }}</span>
                                            </small>
                                        </div>
                                        <span class="badge badge-warning badge-pill">Rp{{ number_format($p->price, 0, ',', '.') }}</span>
                                    </button>
                                @empty
                                    <div class="text-center p-4 text-muted">
                                        <i class="fas fa-box-open d-block mb-2" style="font-size: 24px;"></i>
                                        Produk tidak tersedia.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-md-8 border-left">
                            <div class="section-title mt-0 d-flex justify-content-between">
                                <span>Daftar Belanja</span>
                                <span class="text-muted">Item: {{ count($cart) }}</span>
                            </div>
                            <div class="table-responsive border rounded" style="min-height: 250px; max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-striped table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Produk</th>
                                            <th width="120" class="text-right">Harga</th>
                                            <th width="90" class="text-center">Qty</th>
                                            <th width="130" class="text-right">Subtotal</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cart as $id => $item)
                                            <tr wire:key="cart-{{ $id }}">
                                                <td class="align-middle font-weight-600">{{ $item['name'] }}</td>
                                                <td class="align-middle text-right">{{ number_format($item['price'], 0, ',', '.') }}</td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm text-center" 
                                                        wire:model.live="cart.{{ $id }}.qty" 
                                                        wire:change="calculateTotal" min="1">
                                                </td>
                                                <td class="align-middle text-right font-weight-bold text-dark">
                                                    {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    <button class="btn btn-sm btn-link text-danger p-0" wire:click="removeFromCart({{ $id }})">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">Belum ada produk yang dipilih.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 p-3 rounded shadow-sm" style="background: #2d3436; color: #fff;">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <small class="text-uppercase text-light font-weight-bold" style="letter-spacing: 1px;">Total Pembayaran</small>
                                        <h1 class="mb-0" style="font-size: 2.5rem; color: #fab1a0;">
                                            <small style="font-size: 1rem;">Rp</small>{{ number_format($total_price, 0, ',', '.') }}
                                        </h1>
                                    </div>
                                    <div class="col-md-7 border-left" style="border-color: rgba(255,255,255,0.1) !important;">
                                        <div class="form-group mb-2">
                                            <label class="text-light small text-uppercase">Nominal Pembayaran</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-transparent text-white border-white">Rp</span>
                                                </div>
                                                <input type="number" class="form-control bg-transparent text-white border-white text-right" 
                                                    wire:model.live="pay" placeholder="0" style="font-size: 1.5rem;">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-light small text-uppercase">Kembalian:</span>
                                            <h4 class="mb-0 {{ $change < 0 ? 'text-danger' : 'text-success' }}">
                                                Rp {{ number_format($change, 0, ',', '.') }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-danger shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary shadow-sm" 
                            wire:click="store" 
                            {{ empty($cart) || $change < 0 ? 'disabled' : '' }}>
                        <i class="fas fa-save mr-2"></i> Simpan Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL PENJUALAN --}}
    <div wire:ignore.self class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                
                <div class="modal-body pt-0" id="printArea">
                    <div class="dash"></div>
                    @if($selectedShopping)
                        <div class="text-center mb-4">
                            <h5 class="mb-0 font-weight-bold">STRUK PEMBELIAN</h5>
                            <small class="text-bold badge badge-info mt-2">{{ $selectedShopping->invoice }}</small>
                        </div>

                        <div class="d-flex justify-content-between mb-1">
                            <span>Tanggal:</span>
                            <span>{{ $selectedShopping->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span>Kasir:</span>
                            <span class="font-weight-bold">{{ $selectedShopping->user->name }}</span>
                        </div>

                        <table class="table table-sm table-borderless">
                            <thead>
                                <tr class="border-bottom">
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedShopping->details as $detail)
                                    <tr>
                                        <td>
                                            {{ $detail->product->name_prd }}<br>
                                            <small>@ Rp{{ number_format($detail->price, 0, ',', '.') }}</small>
                                        </td>
                                        <td class="text-center align-middle">{{ $detail->qty }}</td>
                                        <td class="text-right font-weight-bold align-middle">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="border-top mt-3 pt-2">
                            <div class="d-flex justify-content-between font-weight-bold" style="font-size: 1.1rem;">
                                <span>TOTAL PEMBAYARAN</span>
                                <span class="text-danger">Rp{{ number_format($selectedShopping->total_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>Nominal Pembayaran</span>
                                <span class="text-primary">Rp{{ number_format($selectedShopping->pay, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between font-weight-bold font-weight-600">
                                <span>Kembalian</span>
                                <span class="text-success">Rp{{ number_format($selectedShopping->change, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="line">
                            <div class="dash"></div>
                            <p class="text-center font-weight-bold mt-2">Terima Kasih Atas Kunjungan Anda</p>
                            <div class="dash"></div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer bg-whitesmoke" style="margin-top: -30px">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="printStruk()">
                        <i class="fas fa-print mr-1"></i> Print Struck
                    </button>
                    @if($selectedShopping)
                        <a href="{{ route('struck.shopping.pdf', $selectedShopping->id) }}" target="_blank" class="btn btn-success">
                            <i class="fas fa-download mr-1"></i> Download Struk
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('close-modal', event => {
        $('#modalTambahPenjualan').modal('hide');
    });
</script>
@endpush