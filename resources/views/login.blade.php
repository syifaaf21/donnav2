@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="login-card text-center">

                    <!-- Logo -->
                    <img src="{{ asset('images/satu aisin.jfif') }}" alt="Logo" class="logo">

                    <!-- Title -->
                    <div class="login-title">Sign in to your account</div>

                    <!-- Login form -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- NPK -->
                        <div class="mb-3 text-start">
                            <label for="npk" class="form-label">NPK</label>
                            <input
                                type="text"
                                name="npk"
                                id="npk"
                                class="form-control @error('npk') is-invalid @enderror"
                                value="{{ old('npk') }}"
                                required autofocus>

                            @error('npk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3 text-start">
                            <label for="password" class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required>

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3 form-check text-start">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>

                    <!-- Forgot password -->
                    <div class="mt-2">
                        <a href="#">Forgot your password?</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
