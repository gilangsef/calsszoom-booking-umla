<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ZoomService
{
    // Mode Testing (Ubah jadi false kalau nanti sudah pakai akun kampus)
    protected bool $isTestingMode = true;

    public function createMeeting($booking)
    {
        // JIKA MODE TESTING AKTIF (Tidak butuh akun asli)
        if ($this->isTestingMode) {
            return [
                'join_url' => 'https://zoom.us/j/9876543210?pwd=dummy_password_testing',
                'id' => '9876543210',
                'password' => 'DEV123',
            ];
        }

        // JIKA NANTI SUDAH DAPAT KUNCI DARI KAMPUS (Tinggal ganti $isTestingMode = false)
        try {
            $response = Http::asForm()->withBasicAuth(
                env('ZOOM_CLIENT_ID'),
                env('ZOOM_CLIENT_SECRET')
            )->post("https://zoom.us/oauth/token", [
                'grant_type' => 'account_credentials',
                'account_id' => env('ZOOM_ACCOUNT_ID'),
            ]);

            $token = $response->json()['access_token'];

            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);
            $duration = $start->diffInMinutes($end);

            $meeting = Http::withToken($token)->post("https://api.zoom.us/v2/users/me/meetings", [
                'topic' => $booking->purpose,
                'type' => 2,
                'start_time' => Carbon::parse($booking->booking_date . ' ' . $booking->start_time)->format('Y-m-d\TH:i:s'),
                'duration' => $duration,
                'timezone' => 'Asia/Jakarta',
            ]);

            return $meeting->json();
        } catch (\Exception $e) {
            throw new \Exception('Gagal menghubungi Server Zoom Asli.');
        }
    }



    // Ambil Token Akses dari Zoom
    // protected function getAccessToken()
    // {
    //     $response = Http::asForm()->withBasicAuth(
    //         env('ZOOM_CLIENT_ID'),
    //         env('ZOOM_CLIENT_SECRET')
    //     )->post("https://zoom.us/oauth/token", [
    //         'grant_type' => 'account_credentials',
    //         'account_id' => env('ZOOM_ACCOUNT_ID'),
    //     ]);

    //     return $response->json()['access_token'];
    // }

    // // Fungsi Membuat Meeting
    // public function createMeeting($booking)
    // {
    //     $token = $this->getAccessToken();

    //     // Hitung durasi dalam menit
    //     $start = Carbon::parse($booking->start_time);
    //     $end = Carbon::parse($booking->end_time);
    //     $duration = $start->diffInMinutes($end);

    //     $response = Http::withToken($token)->post("https://api.zoom.us/v2/users/me/meetings", [
    //         'topic' => $booking->purpose,
    //         'type' => 2, // Scheduled Meeting
    //         'start_time' => Carbon::parse($booking->booking_date . ' ' . $booking->start_time)->format('Y-m-d\TH:i:s'),
    //         'duration' => $duration,
    //         'timezone' => 'Asia/Jakarta',
    //         'settings' => [
    //             'host_video' => true,
    //             'participant_video' => true,
    //             'join_before_host' => true,
    //             'waiting_room' => false,
    //         ],
    //     ]);

    //     return $response->json();
    // }
}
