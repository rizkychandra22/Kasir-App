<?php

namespace App\Exports;

use App\Models\Shopping;
use App\Services\SalesProfitService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class xlsReportShopping implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithColumnWidths, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $collection;
    protected $summary;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        if ($this->collection === null) {
            $this->collection = SalesProfitService::getSalesData($this->startDate, $this->endDate);
            $this->summary = SalesProfitService::getSummaryData($this->collection, $this->startDate, $this->endDate);
        }
        return $this->collection;
    }

    public function getSummary(): array
    {
        if ($this->summary === null) {
            $this->collection();
        }
        return $this->summary;
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
            'HPP',
            'Laba Kotor',
            'Nominal Bayar',
            'Kembalian',
        ];
    }

    public function map($shopping): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $isQris = ($shopping->payment_method ?? 'cash') === 'qris';

        return [
            $rowNumber,
            $shopping->invoice,
            $shopping->sales_type_label,
            $shopping->created_at ? $shopping->created_at->format('d/m/Y H:i') : '-',
            $shopping->user->name ?? '-',
            $shopping->total_price,
            $shopping->hpp,
            $shopping->gross_profit,
            $isQris ? '-' : $shopping->pay,
            $isQris ? '-' : $shopping->change,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"Rp"#,##0.00',
            'G' => '"Rp"#,##0.00',
            'H' => '"Rp"#,##0.00',
            'I' => '"Rp"#,##0.00',
            'J' => '"Rp"#,##0.00',
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
            'I' => 18,
            'J' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $summary = $this->getSummary();
                $highestRow = $sheet->getHighestRow();

                // Append empty row and then summary section
                $r = $highestRow + 2;

                $sheet->setCellValue("E{$r}", 'RINGKASAN KEUANGAN');
                $sheet->getStyle("E{$r}")->getFont()->setBold(true);
                $r++;

                $sheet->setCellValue("E{$r}", 'Total Transaksi');
                $sheet->setCellValue("F{$r}", $summary['total_transactions'] . ' Transaksi');
                $r++;

                $sheet->setCellValue("E{$r}", 'Total Omzet');
                $sheet->setCellValue("F{$r}", $summary['total_omzet']);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $r++;

                $sheet->setCellValue("E{$r}", '  • Penjualan Cash');
                $sheet->setCellValue("F{$r}", $summary['penjualan_cash'] ?? 0);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $r++;

                $sheet->setCellValue("E{$r}", '  • Penjualan QRIS');
                $sheet->setCellValue("F{$r}", $summary['penjualan_qris'] ?? 0);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $r++;

                $sheet->setCellValue("E{$r}", '  • Penjualan Online');
                $sheet->setCellValue("F{$r}", $summary['penjualan_online'] ?? 0);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $r++;

                $sheet->setCellValue("E{$r}", 'Total HPP');
                $sheet->setCellValue("F{$r}", $summary['total_hpp']);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $r++;

                $sheet->setCellValue("E{$r}", 'Laba Kotor');
                $sheet->setCellValue("F{$r}", $summary['laba_kotor']);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $r++;

                $sheet->setCellValue("E{$r}", 'Margin Laba Kotor');
                $sheet->setCellValue("F{$r}", number_format($summary['margin_laba_kotor'], 2, ',', '.') . '%');
                $r++;

                $sheet->setCellValue("E{$r}", 'Total Pengeluaran');
                $sheet->setCellValue("F{$r}", $summary['total_pengeluaran']);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $r++;

                $sheet->setCellValue("E{$r}", 'Laba Bersih');
                $sheet->setCellValue("F{$r}", $summary['laba_bersih']);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $sheet->getStyle("E{$r}:F{$r}")->getFont()->setBold(true);
                $r++;

                $sheet->setCellValue("E{$r}", 'Margin Laba Bersih');
                $sheet->setCellValue("F{$r}", number_format($summary['margin_laba_bersih'], 2, ',', '.') . '%');
                $sheet->getStyle("E{$r}:F{$r}")->getFont()->setBold(true);
            },
        ];
    }
}
