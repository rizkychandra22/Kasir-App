<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Shopping;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function struckShopping($id)
    {
        $data = Shopping::with(['details.product', 'user'])->findOrFail($id);

        $width  = 226; // 58mm
        $height = 300 + ($data->details->count() * 35);

        $pdf = Pdf::loadView('pdf.shopping-struck', compact('data'))
                ->setPaper([0, 0, $width, $height], 'portrait'); 

        return $pdf->stream('Struk-'.$data->invoice.'.pdf'); 
    }
}
