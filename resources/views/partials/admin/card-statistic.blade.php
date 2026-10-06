@php
    $displayPeriod = $selectedPeriod ?? 'today';
    $labelPeriod = $periodLabel ?? 'Hari Ini';
    $valSales = $periodSales ?? $penjualanHariIni ?? 0;
    $valTransactions = $periodTransactions ?? $transaksiHariIni ?? 0;
    $valSoldQty = $periodSoldQty ?? $produkTerjualHariIni ?? 0;
    $valGrossProfit = $periodGrossProfit ?? $labaKotorHariIni ?? 0;
@endphp

<div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
    <div class="card card-statistic-1 shadow-sm mb-0">
        <div class="card-icon bg-primary text-white">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="card-wrap">
            <div class="card-header">
                <h4>Penjualan {{ $displayPeriod == 'today' ? 'Hari Ini' : "($labelPeriod)" }}</h4>
            </div>
            <div class="card-body font-weight-bold">
                Rp{{ number_format($valSales, 0, ',', '.') }}
            </div>
            <div class="small text-muted px-3 pb-2">
                Total omzet valid
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
    <div class="card card-statistic-1 shadow-sm mb-0">
        <div class="card-icon bg-info text-white">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="card-wrap">
            <div class="card-header">
                <h4>Transaksi {{ $displayPeriod == 'today' ? 'Hari Ini' : "($labelPeriod)" }}</h4>
            </div>
            <div class="card-body font-weight-bold">
                {{ number_format($valTransactions, 0, ',', '.') }}
            </div>
            <div class="small text-muted px-3 pb-2">
                Transaksi berhasil
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
    <div class="card card-statistic-1 shadow-sm mb-0">
        <div class="card-icon bg-warning text-white">
            <i class="fas fa-box-open"></i>
        </div>
        <div class="card-wrap">
            <div class="card-header">
                <h4>Produk Terjual {{ $displayPeriod == 'today' ? 'Hari Ini' : "($labelPeriod)" }}</h4>
            </div>
            <div class="card-body font-weight-bold">
                {{ number_format($valSoldQty, 0, ',', '.') }}
            </div>
            <div class="small text-muted px-3 pb-2">
                Total item produk terjual
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
    <div class="card card-statistic-1 shadow-sm mb-0">
        <div class="card-icon bg-success text-white">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="card-wrap">
            <div class="card-header">
                <h4>Laba Kotor {{ $displayPeriod == 'today' ? 'Hari Ini' : "($labelPeriod)" }}</h4>
            </div>
            <div class="card-body font-weight-bold">
                Rp{{ number_format($valGrossProfit, 0, ',', '.') }}
            </div>
            <div class="small text-muted px-3 pb-2">
                Omzet &minus; HPP Transaksi
            </div>
        </div>
    </div>
</div>