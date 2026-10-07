@php
    $displayPeriod = $selectedPeriod ?? 'today';
    $labelPeriod = $periodLabel ?? 'Hari Ini';
    $valSales = $periodSales ?? $penjualanHariIni ?? 0;
    $valTransactions = $periodTransactions ?? $transaksiHariIni ?? 0;
    $valSoldQty = $periodSoldQty ?? $produkTerjualHariIni ?? 0;
    $valGrossProfit = $periodGrossProfit ?? $labaKotorHariIni ?? 0;
@endphp

<div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-4">
    <div class="card card-statistic-1 h-100 mb-0 shadow-sm" style="border: 1px solid #eef2f6; border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease;">
        <div class="card-icon text-white" style="background-color: #13295C !important; border-radius: 10px 0 0 10px;">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="card-wrap">
            <div class="card-header pt-3">
                <h4 class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Penjualan {{ $displayPeriod == 'today' ? 'Hari Ini' : "($labelPeriod)" }}</h4>
            </div>
            <div class="card-body font-weight-bold text-dark" style="font-size: 1.45rem; line-height: 1.2;">
                Rp{{ number_format($valSales, 0, ',', '.') }}
            </div>
            <div class="small text-muted px-3 pb-2 pt-1" style="font-size: 11.5px;">
                <i class="fas fa-check-circle text-success mr-1"></i>Total omzet valid
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-4">
    <div class="card card-statistic-1 h-100 mb-0 shadow-sm" style="border: 1px solid #eef2f6; border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease;">
        <div class="card-icon bg-info text-white" style="border-radius: 10px 0 0 10px;">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="card-wrap">
            <div class="card-header pt-3">
                <h4 class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Transaksi {{ $displayPeriod == 'today' ? 'Hari Ini' : "($labelPeriod)" }}</h4>
            </div>
            <div class="card-body font-weight-bold text-dark" style="font-size: 1.45rem; line-height: 1.2;">
                {{ number_format($valTransactions, 0, ',', '.') }}
            </div>
            <div class="small text-muted px-3 pb-2 pt-1" style="font-size: 11.5px;">
                <i class="fas fa-check-circle text-info mr-1"></i>Transaksi berhasil
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-4">
    <div class="card card-statistic-1 h-100 mb-0 shadow-sm" style="border: 1px solid #eef2f6; border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease;">
        <div class="card-icon bg-warning text-white" style="border-radius: 10px 0 0 10px;">
            <i class="fas fa-box-open"></i>
        </div>
        <div class="card-wrap">
            <div class="card-header pt-3">
                <h4 class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Produk Terjual {{ $displayPeriod == 'today' ? 'Hari Ini' : "($labelPeriod)" }}</h4>
            </div>
            <div class="card-body font-weight-bold text-dark" style="font-size: 1.45rem; line-height: 1.2;">
                {{ number_format($valSoldQty, 0, ',', '.') }}
            </div>
            <div class="small text-muted px-3 pb-2 pt-1" style="font-size: 11.5px;">
                <i class="fas fa-check-circle text-warning mr-1"></i>Total item terjual
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-4">
    <div class="card card-statistic-1 h-100 mb-0 shadow-sm" style="border: 1px solid #eef2f6; border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease;">
        <div class="card-icon bg-success text-white" style="border-radius: 10px 0 0 10px;">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="card-wrap">
            <div class="card-header pt-3">
                <h4 class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Laba Kotor {{ $displayPeriod == 'today' ? 'Hari Ini' : "($labelPeriod)" }}</h4>
            </div>
            <div class="card-body font-weight-bold text-dark" style="font-size: 1.45rem; line-height: 1.2;">
                Rp{{ number_format($valGrossProfit, 0, ',', '.') }}
            </div>
            <div class="small text-muted px-3 pb-2 pt-1" style="font-size: 11.5px;">
                <i class="fas fa-check-circle text-success mr-1"></i>Omzet &minus; HPP Transaksi
            </div>
        </div>
    </div>
</div>