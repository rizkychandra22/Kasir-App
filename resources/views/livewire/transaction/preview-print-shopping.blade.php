<div>
    <section class="section">
        <div class="section-header">
            <h1>{{ $subpage }}</h1>
            @include('partials.breadcrumb')
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ $content }}</h4>
                        <div class="card-header-action">
                            <div class="btn-group">
                                <a href="{{ route('data.shopping.excel', array_filter(['start_date' => $start_date, 'end_date' => $end_date])) }}" target="_blank" class="btn btn-success">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>
                                <a href="{{ route('data.shopping.print', array_filter(['start_date' => $start_date, 'end_date' => $end_date])) }}" target="_blank" class="btn btn-info">
                                    <i class="fas fa-print mr-1"></i> Print
                                </a>
                                <a href="{{ route('data.shopping.pdf', array_filter(['start_date' => $start_date, 'end_date' => $end_date])) }}" target="_blank" class="btn btn-danger">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filter Periode -->
                        <div class="row mb-4 align-items-end">
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i> Tanggal Mulai</label>
                                <input type="date" wire:model.live="start_date" class="form-control">
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i> Tanggal Selesai</label>
                                <input type="date" wire:model.live="end_date" class="form-control">
                            </div>
                            <div class="col-md-3 col-sm-12 mb-2">
                                @if($start_date || $end_date)
                                    <button wire:click="resetFilter" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo mr-1"></i> Reset Filter
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="45" class="text-center">#</th>
                                        <th>No. Invoice</th>
                                        <th class="text-center">Tipe Penjualan</th>
                                        <th>Tanggal Transaksi</th>
                                        <th>Kasir</th>
                                        <th class="text-right">Total Belanja</th>
                                        <th class="text-right">HPP</th>
                                        <th class="text-right">Laba Kotor</th>
                                        <th class="text-right">Nominal Bayar</th>
                                        <th class="text-right">Kembalian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($shoppings as $item)
                                        <tr wire:key="shopping-{{ $item->id }}">
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="font-weight-bold text-primary">{{ $item->invoice }}</td>
                                            <td class="text-center">
                                                @if(($item->sales_type ?? 'offline') === 'online')
                                                    @if(($item->payment_method ?? 'cash') === 'qris')
                                                        <span class="badge badge-success"><i class="fas fa-globe mr-1"></i> ONLINE (QRIS)</span>
                                                    @else
                                                        <span class="badge badge-success"><i class="fas fa-globe mr-1"></i> ONLINE (CASH)</span>
                                                    @endif
                                                @elseif(($item->payment_method ?? 'cash') === 'qris')
                                                    <span class="badge badge-info"><i class="fas fa-qrcode mr-1"></i> OFFLINE (QRIS)</span>
                                                @else
                                                    <span class="badge badge-secondary"><i class="fas fa-store mr-1"></i> OFFLINE (CASH)</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
                                            <td><code>{{ $item->user->name ?? '-' }}</code></td>
                                            <td class="text-right font-weight-bold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp{{ number_format($item->hpp, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold text-success">Rp{{ number_format($item->gross_profit, 0, ',', '.') }}</td>
                                            <td class="text-right">
                                                @if(($item->payment_method ?? 'cash') === 'qris')
                                                    <span class="text-muted">-</span>
                                                @else
                                                    Rp{{ number_format($item->pay, 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold text-muted">
                                                @if(($item->payment_method ?? 'cash') === 'qris')
                                                    <span class="text-muted">-</span>
                                                @else
                                                    Rp{{ number_format($item->change, 0, ',', '.') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">Tidak ada data penjualan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($shoppings->count() > 0)
                                    <tfoot class="thead-light">
                                        <tr class="font-weight-bold">
                                            <th colspan="5" class="text-right">TOTAL:</th>
                                            <th class="text-right text-primary">Rp{{ number_format($summary['total_omzet'], 0, ',', '.') }}</th>
                                            <th class="text-right text-secondary">Rp{{ number_format($summary['total_hpp'], 0, ',', '.') }}</th>
                                            <th class="text-right text-success">Rp{{ number_format($summary['laba_kotor'], 0, ',', '.') }}</th>
                                            <th class="text-right">Rp{{ number_format($summary['total_bayar'], 0, ',', '.') }}</th>
                                            <th class="text-right">Rp{{ number_format($summary['total_kembalian'], 0, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>

                        <!-- Ringkasan Keuangan Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border border-primary shadow-sm">
                                    <div class="card-header bg-primary text-white" style="min-height: auto; padding: 15px 15px;">
                                        <h6 class="m-0 font-weight-bold text-white" style="font-size: 15px; letter-spacing: 0.5px;"><i class="fas fa-chart-line mr-2"></i>RINGKASAN KEUANGAN</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="row no-gutters">
                                            <div class="col-md-6 border-right">
                                                <table class="table table-sm table-striped m-0">
                                                    <tbody>
                                                        <tr>
                                                            <td class="pl-3 font-weight-bold">Total Transaksi</td>
                                                            <td class="text-right pr-3 font-weight-bold">{{ $summary['total_transactions'] }} Transaksi</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pl-3 font-weight-bold">Total Omzet</td>
                                                            <td class="text-right pr-3 font-weight-bold text-primary">Rp{{ number_format($summary['total_omzet'], 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pl-4 text-muted small"><i class="fas fa-money-bill-wave mr-1"></i> Penjualan Cash</td>
                                                            <td class="text-right pr-3 text-muted small font-weight-bold">Rp{{ number_format($summary['penjualan_cash'] ?? 0, 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pl-4 text-muted small"><i class="fas fa-qrcode mr-1"></i> Penjualan QRIS</td>
                                                            <td class="text-right pr-3 text-muted small font-weight-bold">Rp{{ number_format($summary['penjualan_qris'] ?? 0, 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pl-4 text-muted small"><i class="fas fa-globe mr-1"></i> Penjualan Online</td>
                                                            <td class="text-right pr-3 text-muted small font-weight-bold">Rp{{ number_format($summary['penjualan_online'] ?? 0, 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pl-3 font-weight-bold">Total HPP</td>
                                                            <td class="text-right pr-3 font-weight-bold text-secondary">Rp{{ number_format($summary['total_hpp'], 0, ',', '.') }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-sm table-striped m-0">
                                                    <tbody>
                                                        <tr>
                                                            <td class="pl-3 font-weight-bold">Laba Kotor</td>
                                                            <td class="text-right pr-3 font-weight-bold text-success">Rp{{ number_format($summary['laba_kotor'], 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pl-3 font-weight-bold">Margin Laba Kotor</td>
                                                            <td class="text-right pr-3 font-weight-bold text-success">{{ number_format($summary['margin_laba_kotor'], 2, ',', '.') }}%</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pl-3 font-weight-bold">Total Pengeluaran</td>
                                                            <td class="text-right pr-3 font-weight-bold text-danger">Rp{{ number_format($summary['total_pengeluaran'], 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr class="table-success">
                                                            <td class="pl-3 font-weight-bold h6 m-0">Laba Bersih</td>
                                                            <td class="text-right pr-3 font-weight-bold h6 m-0 text-success">Rp{{ number_format($summary['laba_bersih'], 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr class="table-success">
                                                            <td class="pl-3 font-weight-bold h6 m-0">Margin Laba Bersih</td>
                                                            <td class="text-right pr-3 font-weight-bold h6 m-0 text-success">{{ number_format($summary['margin_laba_bersih'], 2, ',', '.') }}%</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
