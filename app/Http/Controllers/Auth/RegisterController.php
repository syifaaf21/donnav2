<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $departments = Department::all();
        return view('register', compact('departments'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'npk' => 'required|numeric|digits:6|unique:users,npk',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',            // minimal 8 karakter
                'regex:/[a-z]/',    // harus ada huruf kecil
                'regex:/[A-Z]/',    // harus ada huruf besar
                'regex:/[0-9]/',    // harus ada angka
                'regex:/[@$!%*?&#_.]/', // harus ada simbol
                'confirmed',        // harus ada field password_confirmation
            ],
            'department' => 'required|exists:tm_departments,id',
        ], [
            'npk.required' => 'NPK is required.',
            'npk.numeric' => 'NPK must be a number.',
            'npk.digits' => 'NPK must be exactly 6 digits.',
            'npk.unique' => 'NPK is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user = User::create([
            'npk' => $validated['npk'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 3,
            'department_id' => $validated['department'],
        ]);

        // attach departments if you have a relationship
        if (method_exists($user, 'departments')) {
            $user->departments()->attach($validated['department']);
        }

        // auto login after register
        // auth()->login($user);

        return redirect()->route('login')->with('success', 'Registration successful!');

    }
}
