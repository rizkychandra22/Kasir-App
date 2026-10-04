<?php

namespace App\Http\Controllers\Export;

use App\Exports\xlsReportProduct;
use App\Exports\xlsReportShopping;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shopping;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

use App\Services\SalesProfitService;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function struckShopping($id)
    {
        $data = Shopping::with(['details.product', 'user'])->findOrFail($id);

        $width  = 226; // 58mm
        $height = 300 + ($data->details->count() * 35);

        $pdf = Pdf::loadView('pdf.struck-shopping', compact('data'))
                ->setPaper([0, 0, $width, $height], 'portrait'); 

        return $pdf->stream('Struk-'.$data->invoice.'.pdf'); 
    }

    public function dataProduct()
    {
        $data = Product::with('category')->latest()->get();
        $pdf = Pdf::loadView('pdf.data-product', [
                    'data' => $data,
                    'isPdf' => true,
                ])
                ->setPaper('a4', 'portrait');

        return $pdf->stream('Data-Produk.pdf');
    }

    public function printDataProduct()
    {
        $data = Product::with('category')->latest()->get();
        return view('pdf.data-product', [
            'data' => $data,
            'isPdf' => false,
        ]);
    }

    public function dataProductExcel()
    {
        return Excel::download(new xlsReportProduct(), 'Data-Produk.xlsx');
    }

    public function dataShopping(Request $request = null)
    {
        $request = $request ?? request();
        $startDate = $request->input('start_date') ?? $request->input('from_date');
        $endDate = $request->input('end_date') ?? $request->input('to_date');

        $data = SalesProfitService::getSalesData($startDate, $endDate);
        $summary = SalesProfitService::getSummaryData($data, $startDate, $endDate);

        $pdf = Pdf::loadView('pdf.data-shopping', [
                    'data' => $data,
                    'summary' => $summary,
                    'isPdf' => true,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ])
                ->setPaper('a4', 'portrait');

        return $pdf->stream('Data-Penjualan.pdf');
    }

    public function printDataShopping(Request $request = null)
    {
        $request = $request ?? request();
        $startDate = $request->input('start_date') ?? $request->input('from_date');
        $endDate = $request->input('end_date') ?? $request->input('to_date');

        $data = SalesProfitService::getSalesData($startDate, $endDate);
        $summary = SalesProfitService::getSummaryData($data, $startDate, $endDate);

        return view('pdf.data-shopping', [
            'data' => $data,
            'summary' => $summary,
            'isPdf' => false,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function dataShoppingExcel(Request $request = null)
    {
        $request = $request ?? request();
        $startDate = $request->input('start_date') ?? $request->input('from_date');
        $endDate = $request->input('end_date') ?? $request->input('to_date');

        return Excel::download(new xlsReportShopping($startDate, $endDate), 'Data-Penjualan.xlsx');
    }
}
