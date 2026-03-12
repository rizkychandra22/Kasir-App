<?php

use App\Http\Controllers\Kasir\PrintController;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\Admin;
use App\Livewire\Dashboard\Kasir;
use App\Livewire\Kasir\DataCategory;
use App\Livewire\Kasir\DataProduct;
use App\Livewire\Kasir\DataShopping;
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

// Route Grup Role Admin
Route::middleware(['RoleUser:Admin'])->prefix('dashboard')->group(function () {
    Route::get('/admin', Admin::class)->name('admin.dashboard');
});

// Route Grup Role Kasir
Route::middleware(['RoleUser:Kasir'])->prefix('dashboard')->group(function () {
    Route::get('/kasir', Kasir::class)->name('kasir.dashboard');
    Route::get('/kasir/product', DataProduct::class)->name('kasir.product');
    Route::get('/kasir/category', DataCategory::class)->name('kasir.category');
    Route::get('/kasir/shopping', DataShopping::class)->name('kasir.shopping');
});

// Route Download Struk PDF
Route::get('/download/pdf/struck/shopping/{id}', [PrintController::class, 'struckShopping'])->name('struck.shopping.pdf');