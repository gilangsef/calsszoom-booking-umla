<?php

namespace App\Filament\Auth;

// INI YANG DIPERBAIKI: Namespace untuk versi terbaru
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Memanggil komponen bawaan profil Filament
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),

                // --- AREA CUSTOM FIELD ---
                // Tampil hanya jika user memiliki role 'student' (mahasiswa)
                TextInput::make('nim')
                    ->label('Nomor Induk Mahasiswa (NIM)')
                    ->disabled() // Dikunci agar tidak bisa diubah
                    ->dehydrated() // Tetap dikirim ke server saat save jika diperlukan
                    ->visible(fn () => Auth::user()->hasRole('student')),

                // Tampil hanya jika user memiliki role 'dosen'
                TextInput::make('nip')
                    ->label('Nomor Induk Pegawai (NIP)')
                    ->disabled() // Dikunci agar tidak bisa diubah
                    ->dehydrated() // Tetap dikirim ke server saat save jika diperlukan
                    ->visible(fn () => Auth::user()->hasRole('dosen')),

                // --- MENGGUNAKAN DEPARTMENT_ID (RELASI) ---
                Select::make('department_id')
                    ->label('Program Studi')
                    ->relationship('department', 'name') // Mengambil nama dari tabel departments
                    ->disabled() // Dikunci agar tidak bisa diubah (paten)
                    ->dehydrated()
                    // Opsional: Hanya tampil di mahasiswa atau dosen
                    ->visible(fn () => Auth::user()->hasRole(['student', 'dosen'])),

                // Contoh: Menambahkan input Nomor HP
                TextInput::make('phone')
                    ->label('Nomor HP')
                    ->tel()
                    ->maxLength(15),
                // -------------------------

                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
