@extends('layouts.app')

@section('content')
    <div class="container min-vh-100 overflow-hidden">
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
                                type="number"
                                name="npk"
                                id="npk"
                                class="form-control @error('npk') is-invalid @enderror"
                                value="{{ old('npk') }}"
                                maxlength="6"
                                oninput="this.value = this.value.slice(0, 6);"
                                required
                                autofocus>
                            @error('npk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3 text-start">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror" required>

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
