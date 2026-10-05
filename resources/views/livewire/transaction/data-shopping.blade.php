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

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ $content }}</h4>
                        <div class="card-header-action">
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalTambahPenjualan" wire:click="resetInput">
                                    <i class="fas fa-plus-circle mr-1"></i> Transaksi Baru
                                </button>
                                <a href="{{ route('kasir.shopping.export') }}" class="btn btn-success" rel="noopener noreferrer">
                                    <i class="fas fa-file-export mr-1"></i> Export
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive" wire:poll.5s>
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>No. Invoice</th>
                                        <th>Tipe Penjualan</th>
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
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td><span class="badge badge-primary">{{ $item->invoice }}</span></td>
                                            <td class="text-center">
                                                @if(($item->sales_type ?? 'offline') === 'online')
                                                    <span class="badge badge-success"><i class="fas fa-globe mr-1"></i> ONLINE</span>
                                                @elseif(($item->payment_method ?? 'cash') === 'qris')
                                                    <span class="badge badge-info"><i class="fas fa-qrcode mr-1"></i> OFFLINE (QRIS)</span>
                                                @else
                                                    <span class="badge badge-secondary"><i class="fas fa-money-bill-wave mr-1"></i> OFFLINE (CASH)</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                            <td><code class="font-weight-bold">{{ $item->user->name ?? '-' }}</code></td>
                                            <td class="font-weight-bold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                                            <td class="font-weight-bold">
                                                @if(($item->payment_method ?? 'cash') === 'qris')
                                                    <span class="text-muted font-weight-normal">-</span>
                                                @else
                                                    Rp{{ number_format($item->pay, 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="font-weight-bold">
                                                @if(($item->payment_method ?? 'cash') === 'qris')
                                                    <span class="text-muted font-weight-normal">-</span>
                                                @else
                                                    Rp{{ number_format($item->change, 0, ',', '.') }}
                                                @endif
                                            </td>
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
                                            <td colspan="9" class="text-center text-muted">Belum ada transaksi.</td>
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
    <div wire:ignore.self class="modal fade" id="modalTambahPenjualan" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document"> 
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-cash-register mr-2"></i> POS System (Sistem Kasir)</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    {{-- SELECTOR TIPE PENJUALAN --}}
                    <div class="card bg-light border mb-3">
                        <div class="card-body p-2 d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold text-dark"><i class="fas fa-tag mr-1 text-warning"></i> Pilih Tipe Penjualan:</span>
                            <div class="btn-group">
                                <button type="button" 
                                        wire:click="setSalesType('offline')"
                                        @if(count($cart) > 0 && $sales_type !== 'offline') 
                                            wire:confirm="Anda akan mengubah tipe penjualan dari ONLINE ke OFFLINE. Harga produk di keranjang akan disesuaikan. Lanjutkan?" 
                                        @endif
                                        class="btn btn-sm {{ $sales_type === 'offline' ? 'btn-secondary font-weight-bold active' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-store mr-1"></i> Penjualan Offline
                                </button>
                                <button type="button" 
                                        wire:click="setSalesType('online')"
                                        @if(count($cart) > 0 && $sales_type !== 'online') 
                                            wire:confirm="Anda akan mengubah tipe penjualan dari OFFLINE ke ONLINE. Harga produk di keranjang akan disesuaikan. Lanjutkan?" 
                                        @endif
                                        class="btn btn-sm {{ $sales_type === 'online' ? 'btn-success font-weight-bold active' : 'btn-outline-success' }}">
                                    <i class="fas fa-globe mr-1"></i> Penjualan Online
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="section-title mt-0 font-weight-bold text-dark">
                                Cari Produk ({{ strtoupper($sales_type) }})
                            </div>
                            <div class="form-group mb-2">
                                <div class="input-group">
                                    <input type="text" class="form-control" wire:model.live="search_prd" placeholder="Nama atau kode produk...">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fas fa-search"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="list-group shadow-sm border rounded" style="max-height: 380px; overflow-y: auto;">
                                @forelse($products as $p)
                                    @php
                                        $priceToUse = $p->getPriceForSalesType($sales_type);
                                        $hasPrice = $p->hasPriceForSalesType($sales_type);
                                    @endphp
                                    <button type="button" wire:click="addToCart({{ $p->id }})" 
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-left-0 border-right-0">
                                        <div>
                                            <div class="font-weight-bold text-primary">{{ $p->name_prd }}</div>
                                            <small class="text-muted"><i class="fas fa-tag mr-1"></i>{{ $p->code_prd }}</small>
                                        </div>
                                        @if($sales_type === 'online' && !$hasPrice)
                                            <span class="badge badge-danger badge-pill">Belum ada harga</span>
                                        @else
                                            <span class="badge badge-{{ $sales_type === 'online' ? 'success' : 'warning' }} badge-pill">
                                                Rp{{ number_format($priceToUse, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </button>
                                @empty
                                    <div class="text-center p-4 text-muted">
                                        <i class="fas fa-box-open d-block mb-2" style="font-size: 24px;"></i>
                                        Tidak ada produk untuk saluran {{ strtoupper($sales_type) }}.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-md-8 border-left">
                            <div class="section-title mt-0 d-flex justify-content-between font-weight-bold text-dark">
                                <span>Daftar Belanja ({{ strtoupper($sales_type) }})</span>
                                <span class="text-muted font-weight-normal">Item: {{ count($cart) }}</span>
                            </div>
                            <div class="table-responsive border rounded" style="min-height: 230px; max-height: 280px; overflow-y: auto;">
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
                                {{-- Pilihan Metode Pembayaran Khusus Transaksi Offline --}}
                                @if($sales_type === 'offline')
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(255,255,255,0.15) !important;">
                                        <span class="text-light small text-uppercase font-weight-bold">
                                            <i class="fas fa-credit-card mr-1 text-warning"></i> Metode Pembayaran:
                                        </span>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    wire:click="setPaymentMethod('cash')" 
                                                    class="btn {{ $payment_method === 'cash' ? 'btn-success font-weight-bold active' : 'btn-outline-light text-white' }}">
                                                <i class="fas fa-money-bill-wave mr-1"></i> CASH
                                            </button>
                                            <button type="button" 
                                                    wire:click="setPaymentMethod('qris')" 
                                                    class="btn {{ $payment_method === 'qris' ? 'btn-info font-weight-bold active' : 'btn-outline-light text-white' }}">
                                                <i class="fas fa-qrcode mr-1"></i> QRIS
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <small class="text-uppercase text-light font-weight-bold" style="letter-spacing: 1px;">Total Pembayaran ({{ strtoupper($sales_type) }})</small>
                                        <h1 class="mb-0" style="font-size: 2.5rem; color: #fab1a0;">
                                            <small style="font-size: 1rem;">Rp</small>{{ number_format($total_price, 0, ',', '.') }}
                                        </h1>
                                        @if($sales_type === 'offline' && $payment_method === 'qris')
                                            <div class="mt-1">
                                                <span class="badge badge-info"><i class="fas fa-qrcode mr-1"></i> METODE: QRIS</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-7 border-left" style="border-color: rgba(255,255,255,0.1) !important;">
                                        @if($sales_type === 'offline' && $payment_method === 'qris')
                                            {{-- AREA INFORMASI & PLACEHOLDER QRIS --}}
                                            <div class="p-2 rounded text-center" style="background: rgba(255,255,255,0.06); border: 1px dashed rgba(255,255,255,0.25);">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-light small text-uppercase font-weight-bold">PEMBAYARAN QRIS</span>
                                                    <span class="badge badge-info font-weight-bold">QRIS</span>
                                                </div>
                                                
                                                <div class="my-2 p-2 bg-light text-dark rounded border d-flex flex-column align-items-center justify-content-center">
                                                    <i class="fas fa-qrcode fa-2x text-secondary mb-1"></i>
                                                    <span class="badge badge-warning text-dark font-weight-bold">[ QRIS BELUM AKTIF ]</span>
                                                    <small class="text-muted mt-1" style="font-size: 11px;">QRIS belum dikonfigurasi & gateway belum aktif</small>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center pt-1 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                                                    <span class="text-light small text-uppercase">TOTAL YANG HARUS DIBAYAR:</span>
                                                    <h4 class="mb-0 text-warning font-weight-bold">
                                                        Rp {{ number_format($total_price, 0, ',', '.') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        @else
                                            {{-- FORM PEMBAYARAN CASH --}}
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
                                        @endif
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
                            {{ empty($cart) || ($sales_type === 'offline' && $payment_method === 'cash' && $change < 0) || ($sales_type === 'online' && $change < 0) ? 'disabled' : '' }}>
                        <i class="fas fa-save mr-2"></i> Simpan Transaksi ({{ strtoupper($sales_type) }})
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
                            <div class="mt-1">
                                <span class="badge badge-{{ ($selectedShopping->sales_type ?? 'offline') === 'online' ? 'success' : (($selectedShopping->payment_method ?? 'cash') === 'qris' ? 'info' : 'secondary') }}">
                                    Penjualan {{ $selectedShopping->sales_type_label }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-1">
                            <span>Tanggal:</span>
                            <span>{{ $selectedShopping->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span>Kasir:</span>
                            <span class="font-weight-bold">{{ $selectedShopping->user->name ?? '-' }}</span>
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
                                            {{ $detail->product->name_prd ?? '-' }}<br>
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
                            @if(($selectedShopping->payment_method ?? 'cash') === 'qris')
                                <div class="d-flex justify-content-between font-weight-bold">
                                    <span>Metode Pembayaran</span>
                                    <span class="badge badge-info font-weight-bold">QRIS</span>
                                </div>
                            @else
                                <div class="d-flex justify-content-between font-weight-bold">
                                    <span>Nominal Pembayaran</span>
                                    <span class="text-primary">Rp{{ number_format($selectedShopping->pay, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between font-weight-bold font-weight-600">
                                    <span>Kembalian</span>
                                    <span class="text-success">Rp{{ number_format($selectedShopping->change, 0, ',', '.') }}</span>
                                </div>
                            @endif
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