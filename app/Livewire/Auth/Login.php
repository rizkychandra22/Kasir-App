<?php

namespace App\Livewire\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public $login_id; 
    public $password;

    public function login()
    {
        $this->resetErrorBag(); 

        $this->validate([
            'login_id' => 'required',
            'password' => 'required',
        ], [
            'login_id.required' => 'Username atau kode nomor karyawan harus diisi.',
            'password.required' => 'Password harus diisi.',
        ]);

        // Cek login via Username
        if (Auth::attempt(['username' => $this->login_id, 'password' => $this->password])) {
            session()->regenerate();
            return $this->redirectUser();
        }

        // Cek login via Kode Karyawan
        if (Auth::attempt(['code' => $this->login_id, 'password' => $this->password])) {
            session()->regenerate();
            return $this->redirectUser();
        }

        $this->addError('loginError', 'Username, Kode Nomor Pengguna, atau Password tidak sesuai.');
        $this->password = ''; 
    }

    protected function redirectUser()
    {
        $user = Auth::user();

        if ($user->role == 'Admin') {
            return redirect()->route('admin.dashboard')->with('info', 'Selamat datang kembali ' . $user->name . ', tetap semangat ya! 😊');
        } elseif ($user->role == 'Kasir') {
            return redirect()->route('kasir.dashboard')->with('info', 'Selamat datang kembali ' . $user->name . ', tetap semangat ya! 😊');
        } else {
            Auth::logout();
            $this->addError('loginError', 'Role pengguna tidak dikenali.');
        }
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}