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
                                <a href="{{ route('data.shopping.excel') }}" target="_blank" class="btn btn-success">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>
                                <a href="{{ route('data.shopping.print') }}" target="_blank" class="btn btn-info">
                                    <i class="fas fa-print mr-1"></i> Print
                                </a>
                                <a href="{{ route('data.shopping.pdf') }}" target="_blank" class="btn btn-danger">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive" wire:poll.5s>
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50" class="text-center">#</th>
                                        <th>No. Invoice</th>
                                        <th class="text-center">Tipe Penjualan</th>
                                        <th>Tanggal Transaksi</th>
                                        <th>Kasir</th>
                                        <th class="text-right">Total Belanja</th>
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
                                                    <span class="badge badge-success"><i class="fas fa-globe mr-1"></i> ONLINE</span>
                                                @else
                                                    <span class="badge badge-secondary"><i class="fas fa-store mr-1"></i> OFFLINE</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
                                            <td><code>{{ $item->user->name ?? '-' }}</code></td>
                                            <td class="text-right font-weight-bold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp{{ number_format($item->pay, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold text-success">Rp{{ number_format($item->change, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Tidak ada data penjualan</td>
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
</div>
