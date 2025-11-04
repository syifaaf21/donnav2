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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'department' => 'required|exists:tm_departments,id',
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
        auth()->login($user);

        return redirect()->route('login');
    }
}
