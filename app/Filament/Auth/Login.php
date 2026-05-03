<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as PagesLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Support\Htmlable;

class Login extends PagesLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // $this->getEmailFormComponent(),
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getLoginFormComponent(): TextInput
    {
        return TextInput::make('login')
            ->label('Email / NIM / NIP')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $loginValue = $data['login'];

        // 1. Cari user berdasarkan email, nim, atau nip
        $user = User::where(function($query) use ($loginValue) {
            $query->where('email', $loginValue)
                ->orWhere('nim', $loginValue)
                ->orWhere('nip', $loginValue);
        })->first();

        // 2. Cek apakah user ada dan apakah statusnya AKTIF
        // Asumsi nama kolom di database adalah 'is_active'
        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'data.login' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        return [
            // Kita kembalikan 'email' karena Laravel Auth secara default mencari kolom ini
            // Jika user ditemukan, kita pakai email aslinya. Jika tidak, masukkan input mentah agar gagal validasi nanti.
            'email' => $user ? $user->email : $loginValue,
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([

            'data.login' => 'Kredensial tidak cocok. Silakan periksa kembali akun atau kata sandi Anda.',
            // 'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    // Heading
    public function getHeading(): string | Htmlable
    {
        return 'Selamat Datang'; // Ganti sesuai keinginan
    }

    // Sub Heading
    public function getSubheading(): string | Htmlable | null
    {
        return 'Silakan login menggunakan akun Anda';
    }

    // Custoom Label pada Tombol Login
    public function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction()
                ->label('Masuk Sekarang'), // Sign in
        ];
    }
}
