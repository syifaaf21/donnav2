<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'npk' => 'required|numeric|digits_between:1,6',
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard'); // Ganti sesuai tujuan
        }

        return back()->withErrors([
            'npk' => 'Wrong NPK or Password.',
        ])->onlyInput('npk');
    }
}
