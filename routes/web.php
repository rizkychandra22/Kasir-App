<?php

// Import Class Controller

use App\Http\Controllers\Export\PdfController;

// Import Class Livewire
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\Admin;
use App\Livewire\Dashboard\Kasir;
use App\Livewire\Material\DataBahan;
use App\Livewire\Product\DataCategory;
use App\Livewire\Product\DataProduct;
use App\Livewire\Transaction\DataShopping;
use App\Livewire\Transaction\PreviewPrintShopping;
use App\Livewire\Product\PreviewPrintProduct;
use App\Livewire\Operational\TargetPenjualan;
use App\Livewire\Operational\DataLabor;
use App\Livewire\Operational\DataOverhead;
use App\Livewire\Operational\HppProduct;
use App\Livewire\User\Profile as UserProfile;

// Import Class Global
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route Login Full Livewire
Route::redirect('/', '/login');
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/logout', Login::class)->name('logout');   

// Route Pintu Masuk Utama (/user) setelah login
Route::middleware(['RoleUser:Admin,Kasir'])->get('/user', function () {
    $user = Auth::user();
    if ($user->role === 'Admin') {
        return redirect()->route('admin.dashboard');
    } 
    if ($user->role === 'Kasir') {
        return redirect()->route('kasir.dashboard');
    }
    
    Auth::logout();
    return redirect()->route('login')->withErrors([
        'loginAkses' => 'Sesi login anda telah berakhir, silahkan login kembali.'
    ]);
});

// Route Khusus Role Admin
Route::middleware(['RoleUser:Admin'])->prefix('dashboard')->group(function () {
    Route::get('/admin', Admin::class)->name('admin.dashboard');

    // Menu Biaya & Target HPP (Khusus Admin)
    Route::get('/kasir/target-penjualan', TargetPenjualan::class)->name('kasir.target-penjualan');
    Route::get('/kasir/labor', DataLabor::class)->name('kasir.labor');
    Route::get('/kasir/overhead', DataOverhead::class)->name('kasir.overhead');
    Route::get('/kasir/hpp-product', HppProduct::class)->name('kasir.hpp-product');
});

// Route Khusus Role Kasir
Route::middleware(['RoleUser:Kasir'])->prefix('dashboard')->group(function () {
    Route::get('/kasir', Kasir::class)->name('kasir.dashboard');
});

// Route Bersama (Dapat diakses oleh Admin & Kasir)
Route::middleware(['RoleUser:Admin,Kasir'])->prefix('dashboard')->group(function () {
    Route::get('/kasir/bahan', DataBahan::class)->name('kasir.bahan');
    Route::get('/kasir/product', DataProduct::class)->name('kasir.product');
    Route::get('/kasir/product/export', PreviewPrintProduct::class)->name('kasir.product.export');
    Route::get('/kasir/category', DataCategory::class)->name('kasir.category');
    Route::get('/kasir/shopping', DataShopping::class)->name('kasir.shopping');
    Route::get('/kasir/shopping/export', PreviewPrintShopping::class)->name('kasir.shopping.export');
    Route::get('/profile', UserProfile::class)->name('user.profile');
});

// Route Download PDF & Excel
Route::get('/download/pdf/struck/shopping/{id}', [PdfController::class, 'struckShopping'])->name('struck.shopping.pdf');
Route::get('/download/pdf/data-product', [PdfController::class, 'dataProduct'])->name('data.product.pdf');
Route::get('/print/data-product', [PdfController::class, 'printDataProduct'])->name('data.product.print');
Route::get('/download/excel/data-product', [PdfController::class, 'dataProductExcel'])->name('data.product.excel');
Route::get('/download/pdf/data-shopping', [PdfController::class, 'dataShopping'])->name('data.shopping.pdf');
Route::get('/print/data-shopping', [PdfController::class, 'printDataShopping'])->name('data.shopping.print');
Route::get('/download/excel/data-shopping', [PdfController::class, 'dataShoppingExcel'])->name('data.shopping.excel');