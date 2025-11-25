@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="container">
        <div class="bg-white shadow rounded-xl p-6 border border-gray-100">
            <h4 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i data-feather="user" class="w-5 h-5 text-blue-500"></i> My Profile
            </h4>

            <!-- Informasi User -->
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="text-sm font-medium text-gray-600">Name</label>
                    <input type="text" value="{{ Auth::user()->name }}" disabled
                        class="w-full mt-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Email</label>
                    <input type="text" value="{{ Auth::user()->email }}" disabled
                        class="w-full mt-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Role</label>
                    <input type="text" value="{{ Auth::user()->roles->pluck('name')->first() ?? '-' }}" disabled
                        class="w-full mt-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Department</label>
                    <input type="text" value="{{ Auth::user()->departments->pluck('name')->join(', ') ?: '-' }}" disabled
                        class="w-full mt-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700">
                </div>
            </div>

            <hr class="my-6 border-gray-200">

            <!-- Ganti Password -->
            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i data-feather="lock" class="w-5 h-5 text-sky-500"></i> Change Password
            </h3>

            <form method="POST" action="{{ route('profile.updatePassword') }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-gray-600 w-40">Current Password</label>
                        <input type="password" name="current_password" required
                            class="w-full sm:w-64 mt-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-100 focus:border-blue-400">
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-gray-600 w-40">New Password</label>
                        <input type="password" name="new_password" required
                            class="w-full sm:w-64 mt-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-100 focus:border-blue-400">
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-gray-600 w-40">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" required
                            class="w-full sm:w-64 mt-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-100 focus:border-blue-400">
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                            Update Password
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            feather.replace();
        });
    </script>
@endsection
