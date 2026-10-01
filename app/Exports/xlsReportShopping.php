<?php

namespace App\Exports;

use App\Models\Shopping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class xlsReportShopping implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithColumnWidths
{
    public function collection()
    {
        return Shopping::with(['user', 'details.product'])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Invoice',
            'Tipe Penjualan',
            'Tanggal Transaksi',
            'Kasir',
            'Total Belanja',
            'Nominal Bayar',
            'Kembalian',
        ];
    }

    public function map($shopping): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $shopping->invoice,
            strtoupper($shopping->sales_type ?? 'OFFLINE'),
            $shopping->created_at ? $shopping->created_at->format('d/m/Y H:i') : '-',
            $shopping->user->name ?? '-',
            $shopping->total_price,
            $shopping->pay,
            $shopping->change,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"Rp"#,##0.00',
            'G' => '"Rp"#,##0.00',
            'H' => '"Rp"#,##0.00',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'B' => 20,
            'D' => 20,
            'F' => 18,
            'G' => 18,
            'H' => 18,
        ];
    }
}
