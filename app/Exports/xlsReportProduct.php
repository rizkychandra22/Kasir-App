<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class xlsReportProduct implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithColumnWidths
{
    public function collection()
    {
        return Product::with('category')->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kategori',
            'Produk',
            'Kode',
            'Harga',
            'Stok',
        ];
    }

    public function map($product): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $product->category->name ?? '-',
            $product->name_prd,
            $product->code_prd,
            $product->price,
            $product->stock,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '"Rp"#,##0.00',
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'E' => 15,
        ];
    }
}
