<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Data Produk</title>
        @php
            $isPdf = $isPdf ?? false;
        @endphp
        <style>
            @page { margin: 75px; }

            body {
                font-family: 'Courier New', monospace;
                font-size: 11px;
                line-height: 1.4;
                width: 100%;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                font-size: 11px;
            }

            td, th {
                word-wrap: break-word;
                padding: 6px 4px;
                border: 1px solid #000;
            }

            .text-left { text-align: left; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .font-weight-bold { font-weight: bold; }

            .header { margin-bottom: 30px; }
            .title { font-size: 16px; margin: 0; }
            .subtitle { margin: 6px 0 0; }

            .dash {
                margin-top: 12px;
                margin-bottom: 12px;
                border-top: 1px dashed #6d6a6a;
            }

            .info-table td {
                border: none;
                padding: 2px 0;
            }

            .product-table { margin-bottom: {{ $isPdf ? '20px' : '30px' }}; }

            .product-table thead th {
                background: #f3f3f3;
            }

            .footer-text {
                margin: 0;
            }
        </style>
    </head>
    <body>
        <div class="header text-center">
            <div class="dash"></div>
            <h5 class="title font-weight-bold">DATA PRODUK</h5>
            <p class="subtitle">Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
            <p class="subtitle">Total Data: {{ $data->count() }} produk</p>
            <div class="dash"></div>
        </div>

        <table class="product-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 6%;">#</th>
                    <th class="text-left" style="width: 22%;">Kategori</th>
                    <th class="text-left" style="width: 32%;">Produk</th>
                    <th class="text-center" style="width: 12%;">Kode</th>
                    <th class="text-right" style="width: 14%;">Harga</th>
                    <th class="text-center" style="width: 14%;">Stok</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $product)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td>{{ $product->name_prd }}</td>
                        <td class="text-center font-weight-bold">{{ $product->code_prd }}</td>
                        <td class="text-right font-weight-bold">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="text-center font-weight-bold">{{ $product->stock }} pcs</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data produk</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="text-center">
            <div class="dash"></div>
            <p class="font-weight-bold footer-text">Laporan Produk KasirApp</p>
            <div class="dash"></div>
        </div>

        @if (! $isPdf)
            <script type="text/javascript">
                window.onload = function() {
                    window.print();
                };
            </script>
        @endif
    </body>
</html>
