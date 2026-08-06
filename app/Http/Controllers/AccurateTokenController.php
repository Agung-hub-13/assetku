<?php

namespace App\Http\Controllers;

use App\Models\AccurateToken;
use App\Services\AssetSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AccurateTokenController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST TOKEN
    |--------------------------------------------------------------------------
    |*/
    public function index()
    {
        $tokens = AccurateToken::orderBy('expired_at', 'desc')->get();
        return view('admin.accurate_tokens.index', compact('tokens'));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM CREATE
    |--------------------------------------------------------------------------
    |*/
    public function create()
    {
        return view('admin.accurate_tokens.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE MANUAL TOKEN
    |--------------------------------------------------------------------------
    |*/
    public function store(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
            'refresh_token' => 'required|string',
            'expired_at' => 'required|date',
        ]);

        AccurateToken::create([
            'access_token' => $request->access_token,
            'refresh_token' => $request->refresh_token,
            'expired_at' => $request->expired_at,
        ]);

        return redirect()->route('admin.accurate_tokens.index')
            ->with('success', 'Token berhasil disimpan');
    }

    /*
    |--------------------------------------------------------------------------
    | REDIRECT KE ACCURATE
    |--------------------------------------------------------------------------
    |*/
    public function redirectToAccurate()
    {
        $clientId = config('services.accurate.client_id');
        $redirectUri = config('services.accurate.redirect');

        // 2. TAMBAHKAN SCOPE warehouse_view & department_view AGAR BISA TARIK DATA GUDANG & DEPARTEMEN
        $scope = implode(' ', [
            'item_view',
            'item_save',
            'fixed_asset_view',
            'fixed_asset_save',
            'purchase_invoice_view',
            'warehouse_view',    // <- Tambahan wajib untuk lokasi
            'department_view',   // <- Tambahan wajib untuk departemen lokasi
        ]);

        $url = "https://account.accurate.id/oauth/authorize"
            . "?client_id={$clientId}"
            . "&response_type=code"
            . "&redirect_uri={$redirectUri}"
            . "&scope={$scope}";

        return redirect($url);
    }

    public function show($id)
    {
        return redirect()
            ->route('admin.accurate_tokens.index');
    }

    /*
    |--------------------------------------------------------------------------
    | CALLBACK ACCURATE
    |--------------------------------------------------------------------------
    |*/
    public function handleCallback(Request $request)
    {
        logger()->info('CALLBACK ACCURATE', $request->all());

        $code = $request->get('code');

        if (!$code) {
            return back()->with('error', 'Code tidak ditemukan');
        }

        $clientId = config('services.accurate.client_id');
        $clientSecret = config('services.accurate.client_secret');

        $basicAuth = base64_encode("{$clientId}:{$clientSecret}");

        /*
        |--------------------------------------------------------------------------
        | 1. AMBIL ACCESS TOKEN
        |--------------------------------------------------------------------------
        */
        $response = Http::withHeaders([
            'Authorization' => "Basic {$basicAuth}",
            'Accept' => 'application/json',
        ])->asForm()->post(
            'https://account.accurate.id/oauth/token',
            [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('services.accurate.redirect'),
            ]
        );

        $data = $response->json();

        logger()->info('TOKEN RESPONSE', $data);

        if (!isset($data['access_token'])) {
            return back()->with('error', 'Gagal ambil access token');
        }

        $accessToken = $data['access_token'];

        /*
        |--------------------------------------------------------------------------
        | 2. AMBIL DB LIST
        |--------------------------------------------------------------------------
        */
        $dbResponse = Http::withToken($accessToken)
            ->get('https://account.accurate.id/api/db-list.do');

        $dbData = $dbResponse->json();

        logger()->info('DB LIST RESPONSE', $dbData);

        if (
            !isset($dbData['s']) ||
            !$dbData['s'] ||
            empty($dbData['d'])
        ) {
            return back()->with('error', 'Database Accurate tidak ditemukan');
        }

        $database = $dbData['d'][0];
        $dbId = $database['id'] ?? null;

        if (!$dbId) {
            return back()->with('error', 'DB ID tidak ditemukan');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. OPEN DATABASE
        |--------------------------------------------------------------------------
        */
        $openDbResponse = Http::withToken($accessToken)
            ->get(
                'https://account.accurate.id/api/open-db.do',
                [
                    'id' => $dbId
                ]
            );

        $openDbData = $openDbResponse->json();

        logger()->info('OPEN DB RESPONSE', $openDbData);

        if (
            !isset($openDbData['s']) ||
            !$openDbData['s']
        ) {
            return back()->with('error', 'Gagal open database');
        }

        /*
        |--------------------------------------------------------------------------
        | FIX YANG BENAR
        |--------------------------------------------------------------------------
        */
        $session = $openDbData['session'] ?? null;
        $host = $openDbData['host'] ?? 'https://public.accurate.id';

        if (!$session) {
            return back()->with('error', 'Session tidak ditemukan');
        }

        /*
        |--------------------------------------------------------------------------
        | 4. SIMPAN
        |--------------------------------------------------------------------------
        */
        // Kosongkan token lama, ganti yang baru
        AccurateToken::truncate();

        AccurateToken::create([
            'access_token' => $accessToken,
            'refresh_token' => $data['refresh_token'] ?? null,
            'expired_at' => now()->addSeconds(
                $data['expires_in'] ?? 3600
            ),
            'db_id' => $dbId,
            'host' => $host,
            'session' => $session,
        ]);

        // 3. JALANKAN SYNC OTOMATIS SAAT KONEKSI PERTAMA BERHASIL
        // Jalankan sinkronisasi aset bawaan Anda
        return redirect()
            ->route('admin.accurate_tokens.index')
            ->with('success', 'Accurate berhasil connect. Token tersimpan — jalankan sync data via CLI (php artisan accurate:sync-asset).');
    }

    /*
    |--------------------------------------------------------------------------
    | REFRESH TOKEN
    |--------------------------------------------------------------------------
    |*/
    public function refreshToken()
    {
        $token = AccurateToken::first();

        if (!$token) {
            return back()->with('error', 'Token belum ada');
        }

        if (!$token->refresh_token) {
            return back()->with('error', 'Refresh token tidak tersedia');
        }

        $response = Http::asForm()->post('https://account.accurate.id/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'client_id' => config('services.accurate.client_id'),
            'client_secret' => config('services.accurate.client_secret'),
        ]);

        $data = $response->json();

        logger()->info('REFRESH TOKEN RESPONSE', $data);

        if (!isset($data['access_token'])) {
            return back()->with('error', 'Gagal refresh token');
        }

        $token->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $token->refresh_token,
            'expired_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);

        return back()->with('success', 'Token berhasil di-refresh');
    }

    /*
    |--------------------------------------------------------------------------
    | TEST ACCURATE API
    |--------------------------------------------------------------------------
    |*/
    public function testAccurate()
    {
        try {
            $token = AccurateToken::first();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token Accurate tidak ditemukan'
                ], 404);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token->access_token,
                'X-Session-ID' => $token->session,
                'Accept' => 'application/json',
            ])->timeout(30)->get(
                $token->host . '/accurate/api/item/list.do'
            );

            if (!$response->successful()) {
                logger()->error('ACCURATE API ERROR', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'status' => $response->status(),
                    'message' => 'Gagal mengambil data dari Accurate',
                    'error' => $response->body(),
                ], $response->status());
            }

            $data = $response->json();

            return response()->json([
                'success' => true,
                'status' => $response->status(),
                'message' => 'Berhasil mengambil data Accurate',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            logger()->error('TEST ACCURATE ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat koneksi ke Accurate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function testDetail()
    {
        $token = AccurateToken::first();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token->access_token,
            'X-Session-ID' => $token->session,
        ])->get(
            $token->host . '/accurate/api/item/detail.do',
            [
                'id' => 951
            ]
        );

        return response()->json($response->json());
    }
}