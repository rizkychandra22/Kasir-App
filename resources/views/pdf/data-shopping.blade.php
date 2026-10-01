<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Data Penjualan</title>
        @php
            $isPdf = $isPdf ?? false;
            $totalOmset = $data->sum('total_price');
        @endphp
        <style>
            @page { margin: 60px 40px; }

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
                font-size: 10.5px;
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

            .header { margin-bottom: 25px; }
            .title { font-size: 16px; margin: 0; }
            .subtitle { margin: 4px 0 0; }

            .dash {
                margin-top: 12px;
                margin-bottom: 12px;
                border-top: 1px dashed #6d6a6a;
            }

            .shopping-table { margin-bottom: 30px; }
            .shopping-table.pdf-mode { margin-bottom: 20px; }

            .shopping-table thead th {
                background: #f3f3f3;
            }

            .shopping-table tfoot th {
                background: #f9f9f9;
            }

            .footer-text {
                margin: 0;
            }
        </style>
    </head>
    <body>
        <div class="header text-center">
            <div class="dash"></div>
            <h5 class="title font-weight-bold">LAPORAN DATA PENJUALAN</h5>
            <p class="subtitle">Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
            <p class="subtitle">Total Transaksi: {{ $data->count() }} transaksi | Total Omset: Rp{{ number_format($totalOmset, 0, ',', '.') }}</p>
            <div class="dash"></div>
        </div>

        <table class="shopping-table {{ $isPdf ? 'pdf-mode' : '' }}">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">#</th>
                    <th class="text-left" style="width: 22%;">No. Invoice</th>
                    <th class="text-center" style="width: 12%;">Tipe</th>
                    <th class="text-center" style="width: 16%;">Tanggal</th>
                    <th class="text-left" style="width: 15%;">Kasir</th>
                    <th class="text-right" style="width: 16%;">Total</th>
                    <th class="text-right" style="width: 14%;">Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-weight-bold">{{ $item->invoice }}</td>
                        <td class="text-center">{{ strtoupper($item->sales_type ?? 'OFFLINE') }}</td>
                        <td class="text-center">{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $item->user->name ?? '-' }}</td>
                        <td class="text-right font-weight-bold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp{{ number_format($item->pay, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data penjualan</td>
                    </tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right font-weight-bold">TOTAL OMSET:</th>
                        <th class="text-right font-weight-bold">Rp{{ number_format($totalOmset, 0, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="text-center">
            <div class="dash"></div>
            <p class="font-weight-bold footer-text">Laporan Penjualan KasirApp</p>
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
