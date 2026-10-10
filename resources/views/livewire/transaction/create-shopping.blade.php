@push('scripts')
    <style>
        .dash { margin-top: 15px; margin-bottom: 15px; border-top: 1px dashed #6d6a6a; }
        .mode-tab-btn {
            font-size: 1rem;
            font-weight: 700;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .mode-tab-btn.active-tab {
            background: linear-gradient(135deg, #6777ef 0%, #3f51b5 100%);
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(63, 81, 181, 0.35);
        }
        .mode-tab-btn.inactive-tab {
            background: #f4f6f9;
            color: #6c757d;
            border: 1px solid #e2e8f0;
        }
        .mode-tab-btn.inactive-tab:hover {
            background: #e9ecef;
            color: #34395e;
        }
        .product-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }
    </style>
    <script>
        function printSlipMeja() {
            var printContents = document.getElementById('slipPrintArea').innerHTML;
            var printWindow = window.open('', '', 'height=900,width=700');
            printWindow.document.write('<html><head><title>Slip Open Bill</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: "Courier New", Courier, monospace; padding: 15px; width: 300px; font-size: 12px; }');
            printWindow.document.write('.table td, .table th { padding: 3px; font-size: 11px; }');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(printContents);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            setTimeout(function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }, 400);
        }

        function printQuickReceipt() {
            var printContents = document.getElementById('quickReceiptArea').innerHTML;
            var printWindow = window.open('', '', 'height=900,width=700');
            printWindow.document.write('<html><head><title>Struk Pembelian</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: "Courier New", Courier, monospace; padding: 15px; width: 300px; font-size: 12px; }');
            printWindow.document.write('.table td, .table th { padding: 3px; font-size: 11px; }');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(printContents);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            setTimeout(function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }, 400);
        }
    </script>
@endpush

<div>
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h1>{{ $subpage }}</h1>
                @include('partials.breadcrumb')
            </div>
            <div>
                <a href="{{ route('kasir.shopping') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Data Penjualan
                </a>
            </div>
        </div>

        {{-- Alert Notifikasi --}}
        <div class="row">
            <div class="col-12">
                @if (session()->has('success') || session()->has('danger'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-init="setTimeout(() => show = false, 5000)"
                         class="alert alert-{{ session()->has('success') ? 'success' : 'danger' }} alert-dismissible show fade mb-4">
                        <div class="alert-body">
                            <button class="close" @click="show = false"><span>&times;</span></button>
                            {{ session('success') ?? session('danger') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SWITCHER TAB MODE BESAR --}}
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-3 bg-white">
                <div class="d-flex flex-wrap" style="gap: 12px;">
                    <button type="button" 
                            wire:click="setOrderMode('quick')" 
                            class="mode-tab-btn flex-fill text-center {{ $order_mode === 'quick' ? 'active-tab' : 'inactive-tab' }}">
                        <i class="fas fa-bolt mr-2 text-warning"></i> ⚡ Quick Order (Bayar Langsung di Tempat)
                    </button>
                    <button type="button" 
                            wire:click="setOrderMode('open_bill')" 
                            class="mode-tab-btn flex-fill text-center {{ $order_mode === 'open_bill' ? 'active-tab' : 'inactive-tab' }}">
                        <i class="fas fa-receipt mr-2 text-primary"></i> 🍽️ Open Bill (Pesan Dulu / Bayar Nanti)
                    </button>
                </div>
            </div>
        </div>

        {{-- WORKSPACE DUA KOLOM --}}
        <div class="row">
            {{-- KOLOM KIRI: KATALOG PRODUK --}}
            <div class="col-lg-6 col-xl-7">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <h4 class="text-dark font-weight-bold mb-0">
                            <i class="fas fa-coffee text-primary mr-2"></i> Pilih Produk 
                            <span class="badge badge-primary ml-2">{{ count($products) }} Menu</span>
                        </h4>
                    </div>
                    <div class="card-body pt-2">
                        {{-- Filter & Search --}}
                        <div class="row mb-3">
                            <div class="col-md-7 mb-2 mb-md-0">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" 
                                           class="form-control border-left-0" 
                                           wire:model.live.debounce.300ms="search_prd" 
                                           placeholder="Cari nama atau kode produk...">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <select class="form-control" wire:model.live="selected_category">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name_ctg }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Mode Selector Offline / Online khusus Quick Order --}}
                        @if($order_mode === 'quick')
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded border">
                                <span class="font-weight-bold small text-dark"><i class="fas fa-tag mr-1 text-warning"></i> Saluran Penjualan:</span>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" wire:click="setSalesType('offline')" 
                                            class="btn font-weight-bold {{ $sales_type === 'offline' ? 'btn-primary text-white' : 'btn-outline-secondary' }}">
                                        <i class="fas fa-store mr-1"></i> OFFLINE
                                    </button>
                                    <button type="button" wire:click="setSalesType('online')" 
                                            class="btn font-weight-bold {{ $sales_type === 'online' ? 'btn-success text-white' : 'btn-outline-secondary' }}">
                                        <i class="fas fa-globe mr-1"></i> ONLINE
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Grid Menu Produk --}}
                        <div style="max-height: 580px; overflow-y: auto; padding-right: 4px;">
                            <div class="row">
                                @forelse($products as $p)
                                    @php
                                        $effectiveSalesType = $order_mode === 'open_bill' ? 'offline' : $sales_type;
                                        $price = $p->getPriceForSalesType($effectiveSalesType);
                                        $hasPrice = $p->hasPriceForSalesType($effectiveSalesType);
                                    @endphp
                                    <div class="col-md-6 mb-3" wire:key="prod-{{ $p->id }}">
                                        <div class="product-card p-3 bg-white h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="badge badge-light border text-muted small">{{ $p->code_prd }}</span>
                                                    @if($p->category)
                                                        <span class="badge badge-secondary small">{{ $p->category->name_ctg }}</span>
                                                    @endif
                                                </div>
                                                <h6 class="font-weight-bold text-dark mb-1">{{ $p->name_prd }}</h6>
                                                <p class="text-primary font-weight-bold mb-2" style="font-size: 1.05rem;">
                                                    Rp{{ number_format($price, 0, ',', '.') }}
                                                </p>
                                            </div>
                                            <div>
                                                <button type="button" 
                                                        wire:click="addToCart({{ $p->id }})" 
                                                        class="btn btn-block btn-outline-primary btn-sm font-weight-bold">
                                                    <i class="fas fa-plus mr-1"></i> Tambah
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 py-5 text-center text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i>
                                        <p class="font-weight-bold">Tidak ada produk ditemukan.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: FORM PESANAN & PEMBAYARAN --}}
            <div class="col-lg-6 col-xl-5">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h4 class="text-dark font-weight-bold mb-0">
                            @if($order_mode === 'open_bill')
                                <i class="fas fa-clipboard-list text-primary mr-2"></i> Form Open Bill
                            @else
                                <i class="fas fa-cash-register text-warning mr-2"></i> Quick Order Kasir
                            @endif
                        </h4>
                        <button type="button" wire:click="resetCart" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-redo-alt mr-1"></i> Reset
                        </button>
                    </div>

                    <div class="card-body pt-2">
                        {{-- FORM INPUT KHUSUS OPEN BILL --}}
                        @if($order_mode === 'open_bill')
                            <div class="p-3 mb-3 rounded bg-light border">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold text-dark mb-1">
                                        <i class="fas fa-user mr-1 text-primary"></i> Nama Pemesan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('customer_name') is-invalid @enderror" 
                                           wire:model.live="customer_name" 
                                           placeholder="Masukkan nama pemesan (cth: Budi)">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-dark small mb-1">
                                        <i class="fas fa-sticky-note mr-1 text-muted"></i> Catatan Khusus (Opsional)
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model.live="notes" 
                                           placeholder="Cth: Less sugar / Es batu terpisah">
                                </div>
                            </div>
                        @endif

                        {{-- DAFTAR KERANJANG ITEM --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark">Daftar Item</span>
                            <span class="badge badge-info">{{ count($cart) }} Item</span>
                        </div>

                        <div class="table-responsive border rounded mb-3" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-right" width="80">Harga</th>
                                        <th class="text-center" width="85">Qty</th>
                                        <th class="text-right" width="100">Subtotal</th>
                                        <th width="35"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cart as $id => $item)
                                        <tr wire:key="cart-item-{{ $id }}">
                                            <td class="align-middle font-weight-bold">{{ $item['name'] }}</td>
                                            <td class="align-middle text-right">{{ number_format($item['price'], 0, ',', '.') }}</td>
                                            <td class="align-middle text-center">
                                                <input type="number" 
                                                       class="form-control form-control-sm text-center px-1" 
                                                       wire:model.live="cart.{{ $id }}.qty" 
                                                       wire:change="calculateTotal" 
                                                       min="1">
                                            </td>
                                            <td class="align-middle text-right font-weight-bold text-dark">
                                                {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                            </td>
                                            <td class="align-middle text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-link text-danger p-0" 
                                                        wire:click="removeFromCart({{ $id }})">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                Belum ada produk dipilih.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PANEL TOTAL & METODE PEMBAYARAN --}}
                        @if($order_mode === 'open_bill')
                            {{-- TOTAL SEMENTARA OPEN BILL --}}
                            <div class="p-3 rounded mb-3 bg-primary text-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-uppercase" style="letter-spacing: 1px; opacity: 0.9;">Total Pesanan Sementara</small>
                                        <h2 class="mb-0 font-weight-bold">
                                            Rp{{ number_format($total_price, 0, ',', '.') }}
                                        </h2>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-light text-primary font-weight-bold px-2 py-1">
                                            BAYAR DI AKHIR
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" 
                                    wire:click="storeOpenBill" 
                                    class="btn btn-primary btn-lg btn-block font-weight-bold shadow-sm" 
                                    {{ empty($cart) ? 'disabled' : '' }}>
                                <i class="fas fa-save mr-2"></i> Simpan Pesanan (Open Bill)
                            </button>

                        @else
                            {{-- PANEL QUICK ORDER PAYMENT --}}
                            <div class="p-3 rounded mb-3 text-white shadow-sm" style="background: #2d3436;">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: rgba(255,255,255,0.15) !important;">
                                    <span class="small text-uppercase font-weight-bold text-light">Metode Pembayaran:</span>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" wire:click="setPaymentMethod('cash')" 
                                                class="btn {{ $payment_method === 'cash' ? 'btn-success font-weight-bold' : 'btn-outline-light' }}">
                                            <i class="fas fa-money-bill-wave mr-1"></i> CASH
                                        </button>
                                        <button type="button" wire:click="setPaymentMethod('qris')" 
                                                class="btn {{ $payment_method === 'qris' ? 'btn-info font-weight-bold' : 'btn-outline-light' }}">
                                            <i class="fas fa-qrcode mr-1"></i> QRIS
                                        </button>
                                    </div>
                                </div>

                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <small class="text-uppercase text-light font-weight-bold">Total Pembayaran</small>
                                        <h3 class="mb-0 text-warning font-weight-bold">
                                            Rp{{ number_format($total_price, 0, ',', '.') }}
                                        </h3>
                                    </div>
                                    <div class="col-md-7 border-left" style="border-color: rgba(255,255,255,0.1) !important;">
                                        @if($payment_method === 'qris')
                                            <div class="p-2 rounded bg-light text-dark">
                                                <small class="d-block font-weight-bold text-info"><i class="fas fa-qrcode mr-1"></i> QRIS Manual</small>
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input type="checkbox" class="custom-control-input" id="checkQuickQris" wire:model.live="qris_confirmed">
                                                    <label class="custom-control-label small font-weight-bold text-dark" for="checkQuickQris" style="cursor: pointer;">
                                                        Sudah terima pembayaran QRIS
                                                    </label>
                                                </div>
                                                @error('qris_confirmed')
                                                    <span class="text-danger small font-weight-bold">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        @else
                                            <div class="form-group mb-1">
                                                <label class="text-light small text-uppercase mb-0">Nominal Bayar</label>
                                                <input type="number" 
                                                       class="form-control form-control-sm text-right font-weight-bold text-dark" 
                                                       wire:model.live="pay" 
                                                       placeholder="0">
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-light small text-uppercase">Kembalian:</span>
                                                <h5 class="mb-0 {{ $change < 0 ? 'text-danger' : 'text-success' }} font-weight-bold">
                                                    Rp{{ number_format($change, 0, ',', '.') }}
                                                </h5>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <button type="button" 
                                    wire:click="storeQuickOrder" 
                                    class="btn btn-success btn-lg btn-block font-weight-bold shadow-sm" 
                                    {{ empty($cart) || ($payment_method === 'cash' && $change < 0) || ($payment_method === 'qris' && !$qris_confirmed) ? 'disabled' : '' }}>
                                <i class="fas fa-check-circle mr-2"></i> Selesaikan Transaksi (Quick Order)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL SLIP OPEN BILL SEMENTARA --}}
    @if($showSlipModal && $createdOrder)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6);" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-receipt mr-2"></i> Slip Open Bill Sementara</h5>
                        <button type="button" class="close text-white" wire:click="$set('showSlipModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4" id="slipPrintArea">
                        <div class="text-center mb-3">
                            <h4 class="font-weight-bold mb-0">BREW ISLAND COFFEE</h4>
                            <div class="font-weight-bold text-primary mt-1" style="font-size: 1.25rem;">
                                OPEN BILL #{{ $createdOrder->order_number }}
                            </div>
                            <span class="badge badge-warning text-dark font-weight-bold">STATUS: OPEN</span>
                        </div>

                        <div class="dash"></div>

                        <div class="d-flex justify-content-between mb-1">
                            <span>Nama Pemesan:</span>
                            <span class="font-weight-bold">{{ $createdOrder->customer_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Waktu Pesan:</span>
                            <span>{{ $createdOrder->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Kasir:</span>
                            <span>{{ $createdOrder->user->name ?? '-' }}</span>
                        </div>

                        <div class="dash"></div>

                        <table class="table table-sm table-borderless mb-2">
                            <thead>
                                <tr class="border-bottom">
                                    <th>Pesanan</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($createdOrder->details as $d)
                                    <tr>
                                        <td>
                                            {{ $d->product_name ?? ($d->product->name_prd ?? '-') }}<br>
                                            <small class="text-muted">@ Rp{{ number_format($d->unit_price, 0, ',', '.') }}</small>
                                        </td>
                                        <td class="text-center align-middle">{{ $d->qty }}</td>
                                        <td class="text-right font-weight-bold align-middle">
                                            Rp{{ number_format($d->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between font-weight-bold pt-2 border-top" style="font-size: 1.15rem;">
                            <span>TOTAL SEMENTARA</span>
                            <span class="text-primary">Rp{{ number_format($createdOrder->total_amount, 0, ',', '.') }}</span>
                        </div>

                        <div class="dash"></div>

                        <div class="p-2 rounded bg-light text-center small text-muted font-italic mb-3">
                            "Pesanan Anda sedang disiapkan oleh Barista.<br>
                            Selamat menikmati waktu nongkrong di Brew Island!"
                        </div>

                        {{-- KATALOG REFERENSI MENU & HARGA --}}
                        <div class="mt-3 pt-2 border-top">
                            <h6 class="font-weight-bold text-dark text-center small text-uppercase mb-2">
                                — Katalog Referensi Menu Brew Island —
                            </h6>
                            <table class="table table-sm table-striped small mb-0">
                                <tbody>
                                    @foreach($catalogReference as $catPrd)
                                        <tr>
                                            <td>{{ $catPrd->name_prd }}</td>
                                            <td class="text-right font-weight-bold">
                                                Rp{{ number_format($catPrd->price_offline ?? $catPrd->price, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showSlipModal', false)">Tutup</button>
                        <button type="button" class="btn btn-info" onclick="printSlipMeja()">
                            <i class="fas fa-print mr-1"></i> Cetak Slip
                        </button>
                        <a href="{{ route('kasir.shopping') }}" class="btn btn-primary font-weight-bold">
                            <i class="fas fa-list mr-1"></i> Ke Pesanan Aktif
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL STRUK RESMI QUICK ORDER --}}
    @if($showReceiptModal && $createdShopping)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6);" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-check-circle mr-2"></i> Transaksi Berhasil</h5>
                        <button type="button" class="close text-white" wire:click="$set('showReceiptModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4" id="quickReceiptArea">
                        <div class="text-center mb-3">
                            <h4 class="font-weight-bold mb-0">BREW ISLAND COFFEE</h4>
                            <div class="badge badge-info mt-1">{{ $createdShopping->invoice }}</div>
                        </div>

                        <div class="dash"></div>

                        <div class="d-flex justify-content-between mb-1">
                            <span>Tanggal:</span>
                            <span>{{ $createdShopping->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Kasir:</span>
                            <span class="font-weight-bold">{{ $createdShopping->user->name ?? '-' }}</span>
                        </div>

                        <table class="table table-sm table-borderless mb-2">
                            <thead>
                                <tr class="border-bottom">
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($createdShopping->details as $d)
                                    <tr>
                                        <td>
                                            {{ $d->product->name_prd ?? '-' }}<br>
                                            <small class="text-muted">@ Rp{{ number_format($d->price, 0, ',', '.') }}</small>
                                        </td>
                                        <td class="text-center align-middle">{{ $d->qty }}</td>
                                        <td class="text-right font-weight-bold align-middle">
                                            Rp{{ number_format($d->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="border-top pt-2">
                            <div class="d-flex justify-content-between font-weight-bold" style="font-size: 1.15rem;">
                                <span>TOTAL</span>
                                <span class="text-danger">Rp{{ number_format($createdShopping->total_price, 0, ',', '.') }}</span>
                            </div>
                            @if($createdShopping->payment_method === 'qris')
                                <div class="d-flex justify-content-between font-weight-bold">
                                    <span>Metode Pembayaran</span>
                                    <span class="badge badge-info">QRIS</span>
                                </div>
                            @else
                                <div class="d-flex justify-content-between font-weight-bold">
                                    <span>Nominal Bayar</span>
                                    <span>Rp{{ number_format($createdShopping->pay, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between font-weight-bold">
                                    <span>Kembalian</span>
                                    <span class="text-success">Rp{{ number_format($createdShopping->change, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="dash"></div>
                        <p class="text-center font-weight-bold mb-0">Terima Kasih Atas Kunjungan Anda!</p>
                    </div>
                    <div class="modal-footer bg-whitesmoke">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showReceiptModal', false)">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="printQuickReceipt()">
                            <i class="fas fa-print mr-1"></i> Print Struk
                        </button>
                        <a href="{{ route('struck.shopping.pdf', $createdShopping->id) }}" target="_blank" class="btn btn-success">
                            <i class="fas fa-download mr-1"></i> PDF
                        </a>
                        <a href="{{ route('kasir.shopping') }}" class="btn btn-outline-primary">
                            Ke Data Penjualan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
