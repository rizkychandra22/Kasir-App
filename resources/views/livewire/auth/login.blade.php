<div>
    <form wire:submit.prevent="login" style="margin-top: 20px">
        {{-- Alert Error Login Gagal (Username/Password Salah) --}}
        @error('loginError')
            <div class="alert alert-danger alert-block alert-auto-hide" style="text-align:center">
                <strong>Perhatian!</strong>
                <p class="mb-0 text-center">{{ $message }}</p>
            </div>
        @enderror

        {{-- Alert Akses Dilarang dari Middleware --}}
        @if($errors->has('loginAkses'))
            <div class="alert alert-warning alert-block alert-auto-hide" style="text-align:center">
                <strong>Akses Ditolak</strong>
                <p class="mb-0 text-center">{{ $errors->first('loginAkses') }}</p>
            </div>
        @endif

        {{-- Input Username / Kode --}}
        <div class="form-group">
            <label for="login_id">Username / Kode Karyawan</label>
            <input type="text" 
                class="form-control @error('login_id') is-invalid @enderror" 
                wire:model="login_id" 
                placeholder="Masukkan Username atau Kode"
                id="login_id">
            @error('login_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Input Password --}}
        <div class="form-group">
            <div class="d-block">
                <label for="password" class="control-label">Password</label>
            </div>
            <div class="input-group" x-data="{ showPassword: false }">
                <input :type="showPassword ? 'text' : 'password'"
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    wire:model="password" 
                    placeholder="Masukkan Password">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                        @click="showPassword = !showPassword"
                        onclick="toggleLoginPassword(this)"
                        title="Lihat / Sembunyikan Password">
                        <i class="fas fa-eye" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            @error('password')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Tombol Login dengan Loading State --}}
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg btn-block" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Login</span>
                <span wire:loading wire:target="login">
                    <i class="fas fa-spinner fa-spin"></i> Memproses...
                </span>
            </button>
        </div>
    </form>

    {{-- Script khusus untuk interaksi Form --}}
    @push('scripts')
        <script>
            function toggleLoginPassword(buttonEl) {
                // If Alpine is handling this component, let Alpine manage it
                if (window.Alpine && buttonEl && buttonEl._x_dataStack) {
                    return;
                }
                const passwordField = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                if (passwordField) {
                    const isPassword = passwordField.type === 'password';
                    passwordField.type = isPassword ? 'text' : 'password';
                    if (eyeIcon) {
                        if (isPassword) {
                            eyeIcon.classList.remove('fa-eye');
                            eyeIcon.classList.add('fa-eye-slash');
                        } else {
                            eyeIcon.classList.remove('fa-eye-slash');
                            eyeIcon.classList.add('fa-eye');
                        }
                    }
                }
            }

            function initLoginAlerts() {
                setTimeout(() => {
                    document.querySelectorAll('.alert-auto-hide').forEach(el => {
                        el.style.transition = 'opacity 0.5s ease';
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 500);
                    });
                }, 3000);
            }

            document.addEventListener('DOMContentLoaded', initLoginAlerts);
            document.addEventListener('livewire:navigated', initLoginAlerts);
        </script>
    @endpush
</div>