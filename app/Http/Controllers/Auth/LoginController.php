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
        // Validate input
        $credentials = $request->validate([
            'npk' => 'required|numeric|digits:6', // exactly 6 digits
            'password' => 'required|string|min:6', // at least 6 characters
        ], [
            'npk.required' => 'NPK is required.',
            'npk.numeric' => 'NPK must be a number.',
            'npk.digits' => 'NPK must be exactly 6 digits.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        // Attempt login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard'); // Change to your desired route
        }

        // Login failed
        return back()->withErrors([
            'npk' => 'NPK or Password is incorrect.',
        ])->onlyInput('npk');
    }
}
