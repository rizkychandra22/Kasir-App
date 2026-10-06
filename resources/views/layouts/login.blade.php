<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Login Kasir</title>
    <link rel="icon" href="{{ asset('logoBrewIsland.png') }}">

    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/modules/bootstrap-social/bootstrap-social.css') }}">

    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('!template-stisla/dist/assets/css/custom.css') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                        <div class="card card-primary">
                            <div class="card-header justify-content-center text-center pb-0">
                                <a href="/" class="text-center d-flex flex-column align-items-center">
                                    <img src="{{ asset('logoBrewIsland.png') }}" 
                                         alt="Brew Island Logo" style="max-height: 80px; width: auto;" class="img-fluid mb-2">
                                    <h4 class="font-weight-bold mb-0" style="color: #13295C !important;">Brew Island</h4>
                                    <span class="small text-muted font-weight-bold">Coffee & Eatery</span>
                                </a>
                            </div>
                            <div class="card-body" style="margin-top: -35px">
                                <hr>
                                
                                {{ $slot }}

                                <button onclick="window.location.href='/'" class="btn btn-success btn-lg btn-block">Go To Website</button>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="{{ asset('!template-stisla/dist/assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/modules/popper.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/js/stisla.js') }}"></script>
    <script src="{{ asset('!template-stisla/dist/assets/js/scripts.js') }}"></script>
    
    @livewireScripts
    @stack('scripts')
</body>
</html>