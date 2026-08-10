<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Validasi keberadaan user dan kecocokan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah!',
            ], 401);
        }

        // Jika berhasil, kembalikan data user
        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'data' => $user
        ], 200);
    }
}
