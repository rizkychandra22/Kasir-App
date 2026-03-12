<div>
    <section class="section">
        <div class="section-header">
            <h1>{{ $subpage }}</h1>
            @include('partials.breadcrumb')
        </div>

        <div class="row">
            {{-- Alert Message Login --}}
            @if (session('info'))
                <div class="col-12" id="alert-container">
                    <div id="alert" class="alert alert-info alert-dismissible show fade mb-4">
                        <div class="alert-body">
                            {{ session('info') }}
                        </div>
                    </div>
                </div>
            @endif               
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ $content }}</h4>
                        <div class="card-header-action">
                            {{-- <div class="btn-group">
                                <a href="" class="btn btn-danger">Daftar Produk</a>
                                <a href="" class="btn btn-success">Input Produk</a>
                            </div> --}}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="statistic-details mt-sm-4">
                            <div class="statistic-details-item">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            function initDashboardAlert() {
                setTimeout(function() {
                    var infoAlert = document.getElementById('alert');
                    if (infoAlert) {
                        infoAlert.style.transition = 'opacity 0.5s ease-out';
                        infoAlert.style.opacity = '0';
                        setTimeout(function() {
                            infoAlert.remove();
                        }, 500);
                    }
                }, 3000);
            }

            document.addEventListener('livewire:navigated', initDashboardAlert);
            document.addEventListener('DOMContentLoaded', initDashboardAlert);
        </script>
    @endpush
</div>