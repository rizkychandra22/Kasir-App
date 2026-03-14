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
                                <a href="{{ route('data.product.excel') }}" target="_blank" class="btn btn-success">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>
                                <a href="{{ route('data.product.print') }}" target="_blank" class="btn btn-info">
                                    <i class="fas fa-print mr-1"></i> Print
                                </a>
                                <a href="{{ route('data.product.pdf') }}" target="_blank" class="btn btn-danger">
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
                                        <th width="50">#</th>
                                        <th>Kategori</th>
                                        <th>Produk</th>
                                        <th>Kode</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        <tr wire:key="product-{{ $product->id }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $product->category->name }}</td>
                                            <td>{{ $product->name_prd }}</td>
                                            <td class="font-weight-bold">{{ $product->code_prd }}</td>
                                            <td class="font-weight-bold">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                                            <td class="font-weight-bold">{{ $product->stock }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Tidak ada data produk</td>
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
