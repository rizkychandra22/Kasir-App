<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Pembelian - {{ $data->invoice }}</title>
    <style>
        @page { margin: 10px; }
        table { font-size: 11px; }
        table {
            width: 100%;
            table-layout: fixed;
        }
        td, th {
            word-wrap: break-word;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            padding: 0px 0px;
            width: 100%;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        .header { margin-bottom: 20px; }
        .invoice-title { font-size: 16px; margin: 0; }
        .badge { 
            border: 1px solid #000; 
            padding: 2px 5px; 
            display: inline-block; 
            margin-top: 5px;
        }

        .info-table, .item-table, .total-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .border-bottom { border-bottom: 1px solid #000; }
        .border-top { border-top: 1px solid #000; }
        
        .item-table th { border-bottom: 1px solid #000; }
        .item-table td { padding: 5px 0; }
        
        .total-row td { padding: 2px 0; }
        .total-main { font-size: 14px;  } /* Warna merah untuk total */
        
        .dash { margin-top: 20px; margin-bottom: 20px; border-top: 1px dashed #6d6a6a; }
    </style>
</head>
<body>
    <div class="header text-center">
        <div class="dash"></div>
        <h5 class="invoice-title font-weight-bold">STRUK PEMBELIAN</h5>
        <div class="badge">{{ $data->invoice }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td>Tanggal:</td>
            <td class="text-right">{{ $data->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="border-bottom" style="padding-bottom: 5px;">Kasir:</td>
            <td class="text-right border-bottom font-weight-bold" style="padding-bottom: 5px;">{{ $data->user->name }}</td>
        </tr>
    </table>

    <table class="table item-table">
        <thead>
            <tr>
                <th class="text-left">Produk</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->details as $detail)
                <tr>
                    <td>
                        {{ $detail->product->name_prd }}<br>
                        <small>@ Rp{{ number_format($detail->price, 0, ',', '.') }}</small>
                    </td>
                    <td class="text-right">{{ $detail->qty }}</td>
                    <td class="text-right font-weight-bold">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total-table border-top">
        <tr class="total-row">
            <td class="font-weight-bold" style="font-size: 13px; padding-top: 8px;">TOTAL PEMBAYARAN</td>
            <td class="text-right font-weight-bold total-main" style="padding-top: 8px;">Rp{{ number_format($data->total_price, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td class="font-weight-bold">Nominal Pembayaran</td>
            <td class="text-right font-weight-bold">Rp{{ number_format($data->pay, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td class="font-weight-bold">Kembalian</td>
            <td class="text-right font-weight-bold">Rp{{ number_format($data->change, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="text-center">
        <div class="dash"></div>
        <p class="font-weight-bold">Terima Kasih Atas Kunjungan Anda</p>
        <div class="dash"></div>
    </div>
</body>
</html>