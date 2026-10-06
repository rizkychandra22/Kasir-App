<div>
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-user-circle mr-2 text-primary"></i>{{ $subpage }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('kasir.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Profil</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Halo, {{ Auth::user()->name }}!</h2>
            <p class="section-lead">
                Kelola informasi akun dan kata sandi Anda dengan aman di halaman ini.
            </p>

            <div class="row mt-sm-4">
                {{-- Kolom Kiri: Informasi Akun & Avatar --}}
                <div class="col-12 col-md-12 col-lg-5 mb-4">
                    <div class="card profile-widget shadow-sm">
                        <div class="profile-widget-header text-center pt-4">
                            <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-light border shadow-sm" style="width: 100px; height: 100px;">
                                <i class="fas fa-user fa-3x text-primary"></i>
                            </div>
                        </div>
                        <div class="profile-widget-description text-center pb-2">
                            <div class="profile-widget-name font-weight-bold h5 mb-1 text-dark">
                                {{ Auth::user()->name }}
                            </div>
                            <div class="mb-3">
                                <span class="badge {{ Auth::user()->isAdmin() ? 'badge-primary' : 'badge-info' }} px-3 py-1">
                                    <i class="fas {{ Auth::user()->isAdmin() ? 'fa-user-shield' : 'fa-cash-register' }} mr-1"></i>
                                    {{ Auth::user()->role }}
                                </span>
                            </div>

                            <ul class="list-group list-group-flush text-left border-top">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-3">
                                    <span class="text-muted"><i class="fas fa-id-badge mr-2 text-secondary"></i>Kode Karyawan</span>
                                    <span class="font-weight-bold text-dark">{{ $code }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-3">
                                    <span class="text-muted"><i class="fas fa-at mr-2 text-secondary"></i>Username</span>
                                    <span class="font-weight-bold text-dark">{{ $username }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-3">
                                    <span class="text-muted"><i class="fas fa-envelope mr-2 text-secondary"></i>Email Terdaftar</span>
                                    <span class="font-weight-bold text-dark">{{ $email }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer text-center bg-light small text-muted py-2">
                            <i class="fas fa-shield-alt mr-1 text-success"></i>Akun Terotentikasi Sistem
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Form Edit Profil & Ganti Password --}}
                <div class="col-12 col-md-12 col-lg-7">
                    {{-- Form 1: Ubah Data Profil --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4 class="text-dark"><i class="fas fa-user-edit mr-2 text-primary"></i>Edit Data Profil</h4>
                        </div>
                        <div class="card-body">
                            @if (session()->has('profile_success'))
                                <div class="alert alert-success alert-dismissible show fade">
                                    <div class="alert-body">
                                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                        <i class="fas fa-check-circle mr-1"></i>{{ session('profile_success') }}
                                    </div>
                                </div>
                            @endif

                            <form wire:submit.prevent="updateProfile">
                                <div class="form-group">
                                    <label for="name" class="font-weight-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" id="name" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nama Lengkap">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email" class="font-weight-bold">Alamat Email <span class="text-danger">*</span></label>
                                    <input type="email" id="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="Alamat Email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="text-muted small">Username (Read-Only)</label>
                                        <input type="text" class="form-control bg-light" value="{{ $username }}" readonly disabled>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="text-muted small">Role (Read-Only)</label>
                                        <input type="text" class="form-control bg-light" value="{{ $role }}" readonly disabled>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="updateProfile">
                                            <i class="fas fa-save mr-1"></i>Simpan Perubahan
                                        </span>
                                        <span wire:loading wire:target="updateProfile">
                                            <i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Form 2: Ganti Password --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4 class="text-dark"><i class="fas fa-key mr-2 text-warning"></i>Ganti Kata Sandi</h4>
                        </div>
                        <div class="card-body">
                            @if (session()->has('password_success'))
                                <div class="alert alert-success alert-dismissible show fade">
                                    <div class="alert-body">
                                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                        <i class="fas fa-check-circle mr-1"></i>{{ session('password_success') }}
                                    </div>
                                </div>
                            @endif

                            <form wire:submit.prevent="updatePassword">
                                <div class="form-group">
                                    <label for="current_password" class="font-weight-bold">Password Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" id="current_password" wire:model="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Masukkan password saat ini">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="new_password" class="font-weight-bold">Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" id="new_password" wire:model="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Minimal 6 karakter">
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="new_password_confirmation" class="font-weight-bold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" id="new_password_confirmation" wire:model="new_password_confirmation" class="form-control" placeholder="Ulangi password baru">
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="updatePassword">
                                            <i class="fas fa-lock mr-1"></i>Perbarui Password
                                        </span>
                                        <span wire:loading wire:target="updatePassword">
                                            <i class="fas fa-spinner fa-spin mr-1"></i>Memproses...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
