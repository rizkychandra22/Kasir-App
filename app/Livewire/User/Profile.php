<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Profile extends Component
{
    public $subpage = 'Profil Saya';
    public $content = 'Pengaturan Akun Pengguna';

    // Editable profile fields
    public $name;
    public $email;
    public $username;

    // Read-only account info
    public $code;
    public $role;

    // Password fields
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->username = $user->username;
            $this->code = $user->code;
            $this->role = $user->role;
        }
    }

    public function updateProfile()
    {
        $userId = Auth::id();

        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'username' => 'required|string|min:2|max:255|unique:users,username,' . $userId,
        ], [
            'name.required' => 'Nama pengguna harus diisi.',
            'name.min' => 'Nama pengguna minimal 2 karakter.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'username.required' => 'Username harus diisi.',
            'username.min' => 'Username minimal 5 karakter.',
            'username.unique' => 'Username ini sudah digunakan oleh akun lain.',
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->username = $this->username;
        $user->save();

        session()->flash('profile_success', 'Data profil Anda berhasil diperbarui.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini harus diisi.',
            'new_password.required' => 'Password baru harus diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Password saat ini tidak sesuai.');
            return;
        }

        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('password_success', 'Password Anda berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.user.profile')->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}
