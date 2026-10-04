<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Data Penjualan</title>
        @php
            $isPdf = $isPdf ?? false;
            $summary = $summary ?? \App\Services\SalesProfitService::getSummaryData($data, $startDate ?? null, $endDate ?? null);
            $totalOmset = $summary['total_omzet'];
        @endphp
        <style>
            @page { margin: 50px 30px; }

            body {
                font-family: 'Courier New', monospace;
                font-size: 10.5px;
                line-height: 1.4;
                width: 100%;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                font-size: 10px;
            }

            td, th {
                word-wrap: break-word;
                padding: 5px 3px;
                border: 1px solid #000;
            }

            .text-left { text-align: left; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .font-weight-bold { font-weight: bold; }

            .header { margin-bottom: 20px; }
            .title { font-size: 15px; margin: 0; }
            .subtitle { margin: 3px 0 0; }

            .dash {
                margin-top: 10px;
                margin-bottom: 10px;
                border-top: 1px dashed #6d6a6a;
            }

            .shopping-table { margin-bottom: 20px; }
            .shopping-table.pdf-mode { margin-bottom: 15px; }

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
            @if(!empty($startDate) || !empty($endDate))
                <p class="subtitle">Periode: {{ $startDate ? \App\Services\SalesProfitService::parseDate($startDate)->format('d/m/Y') : 'Awal' }} s/d {{ $endDate ? \App\Services\SalesProfitService::parseDate($endDate)->format('d/m/Y') : 'Sekarang' }}</p>
            @endif
            <p class="subtitle">Total Transaksi: {{ $summary['total_transactions'] }} transaksi | Total Omset: Rp{{ number_format($totalOmset, 0, ',', '.') }}</p>
            <div class="dash"></div>
        </div>

        <table class="shopping-table {{ $isPdf ? 'pdf-mode' : '' }}">
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">#</th>
                    <th class="text-left" style="width: 19%;">No. Invoice</th>
                    <th class="text-center" style="width: 9%;">Tipe</th>
                    <th class="text-center" style="width: 14%;">Tanggal</th>
                    <th class="text-left" style="width: 11%;">Kasir</th>
                    <th class="text-right" style="width: 11%;">Total</th>
                    <th class="text-right" style="width: 11%;">HPP</th>
                    <th class="text-right" style="width: 11%;">Laba Kotor</th>
                    <th class="text-right" style="width: 10%;">Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-weight-bold">{{ $item->invoice }}</td>
                        <td class="text-center">{{ $item->sales_type_label }}</td>
                        <td class="text-center">{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $item->user->name ?? '-' }}</td>
                        <td class="text-right font-weight-bold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp{{ number_format($item->hpp, 0, ',', '.') }}</td>
                        <td class="text-right font-weight-bold">Rp{{ number_format($item->gross_profit, 0, ',', '.') }}</td>
                        <td class="text-right">
                            @if(($item->payment_method ?? 'cash') === 'qris')
                                -
                            @else
                                Rp{{ number_format($item->pay, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data penjualan</td>
                    </tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right font-weight-bold">TOTAL:</th>
                        <th class="text-right font-weight-bold">Rp{{ number_format($summary['total_omzet'], 0, ',', '.') }}</th>
                        <th class="text-right font-weight-bold">Rp{{ number_format($summary['total_hpp'], 0, ',', '.') }}</th>
                        <th class="text-right font-weight-bold">Rp{{ number_format($summary['laba_kotor'], 0, ',', '.') }}</th>
                        <th class="text-right font-weight-bold">Rp{{ number_format($summary['total_bayar'], 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>

        <!-- Ringkasan Keuangan Section -->
        <div style="margin-top: 15px; margin-bottom: 20px; page-break-inside: avoid;">
            <table style="width: 55%; margin-left: auto; margin-right: 0; border: 1px solid #000; font-size: 10px;">
                <thead>
                    <tr style="background: #f3f3f3;">
                        <th colspan="2" class="text-left font-weight-bold" style="padding: 5px 8px; border-bottom: 1px solid #000;">
                            RINGKASAN KEUANGAN
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 4px 8px; border: 1px solid #000;">Total Transaksi</td>
                        <td class="text-right font-weight-bold" style="padding: 4px 8px; border: 1px solid #000;">{{ $summary['total_transactions'] }} Transaksi</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; border: 1px solid #000;">Total Omzet</td>
                        <td class="text-right font-weight-bold" style="padding: 4px 8px; border: 1px solid #000;">Rp{{ number_format($summary['total_omzet'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 8px 3px 18px; border: 1px solid #000; color: #555;">&bull; Penjualan Cash</td>
                        <td class="text-right" style="padding: 3px 8px; border: 1px solid #000; color: #555;">Rp{{ number_format($summary['penjualan_cash'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 8px 3px 18px; border: 1px solid #000; color: #555;">&bull; Penjualan QRIS</td>
                        <td class="text-right" style="padding: 3px 8px; border: 1px solid #000; color: #555;">Rp{{ number_format($summary['penjualan_qris'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 8px 3px 18px; border: 1px solid #000; color: #555;">&bull; Penjualan Online</td>
                        <td class="text-right" style="padding: 3px 8px; border: 1px solid #000; color: #555;">Rp{{ number_format($summary['penjualan_online'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; border: 1px solid #000;">Total HPP</td>
                        <td class="text-right font-weight-bold" style="padding: 4px 8px; border: 1px solid #000;">Rp{{ number_format($summary['total_hpp'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; border: 1px solid #000;">Laba Kotor</td>
                        <td class="text-right font-weight-bold" style="padding: 4px 8px; border: 1px solid #000;">Rp{{ number_format($summary['laba_kotor'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; border: 1px solid #000;">Margin Laba Kotor</td>
                        <td class="text-right font-weight-bold" style="padding: 4px 8px; border: 1px solid #000;">{{ number_format($summary['margin_laba_kotor'], 2, ',', '.') }}%</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; border: 1px solid #000;">Total Pengeluaran</td>
                        <td class="text-right font-weight-bold" style="padding: 4px 8px; border: 1px solid #000;">Rp{{ number_format($summary['total_pengeluaran'], 0, ',', '.') }}</td>
                    </tr>
                    <tr style="background: #f9f9f9;">
                        <td class="font-weight-bold" style="padding: 5px 8px; border: 1px solid #000;">Laba Bersih</td>
                        <td class="text-right font-weight-bold" style="padding: 5px 8px; border: 1px solid #000;">Rp{{ number_format($summary['laba_bersih'], 0, ',', '.') }}</td>
                    </tr>
                    <tr style="background: #f9f9f9;">
                        <td class="font-weight-bold" style="padding: 5px 8px; border: 1px solid #000;">Margin Laba Bersih</td>
                        <td class="text-right font-weight-bold" style="padding: 5px 8px; border: 1px solid #000;">{{ number_format($summary['margin_laba_bersih'], 2, ',', '.') }}%</td>
                    </tr>
                </tbody>
            </table>
            <div style="clear: both;"></div>
        </div>

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
