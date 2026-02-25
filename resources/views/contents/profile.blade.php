@extends('layouts.app')

@section('title', 'My Profile')
@section('subtitle', 'Manage Your Profile and Password')

@section('content')
    <div class="container max-w-5xl mx-auto">
        <div class="bg-white/80 backdrop-blur shadow-lg rounded-2xl p-8 border border-gray-100">

            {{-- Alerts --}}
            @if (session('warning'))
                <div class="mb-5 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Profile Header --}}
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i data-feather="user" class="w-5 h-5 text-blue-600"></i>
                </div>
                <h4 class="text-xl font-semibold text-gray-800">My Profile</h4>
            </div>

            {{-- Edit Profile --}}
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Name</label>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required
                            class="w-full mt-2 rounded-xl border border-gray-200 px-4 py-2.5
                               focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600">Email</label>
                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                            class="w-full mt-2 rounded-xl border border-gray-200 px-4 py-2.5
                               focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600">Role</label>
                        <input type="text" value="{{ Auth::user()->roles->pluck('name')->first() ?? '-' }}" disabled
                            class="w-full mt-2 rounded-xl bg-gray-50 border border-gray-200 px-4 py-2.5 text-gray-700">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600">Department</label>
                        <input type="text" value="{{ Auth::user()->departments->pluck('name')->join(', ') ?: '-' }}"
                            disabled
                            class="w-full mt-2 rounded-xl bg-gray-50 border border-gray-200 px-4 py-2.5 text-gray-700">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-blue-700 text-sm font-medium
                           shadow-sm hover:bg-blue-200 hover:shadow-md transition-all duration-150">
                        Save Profile
                    </button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="my-10 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

            {{-- Change Password Header --}}
            <div class="flex items-center gap-3 mb-5">
                <div class="p-2 bg-sky-100 rounded-lg">
                    <i data-feather="lock" class="w-5 h-5 text-sky-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Change Password</h3>
            </div>

            {{-- Change Password --}}
            <form method="POST" action="{{ route('profile.updatePassword') }}">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <label class="text-sm font-medium text-gray-600 w-44">Current Password</label>
                        <div class="relative w-full sm:w-72">
                            <input type="password" name="current_password" required
                                class="password-input w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-11
               focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">

                            <button type="button"
                                class="password-toggle absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i data-feather="eye" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <label class="text-sm font-medium text-gray-600 w-44 pt-2">New Password</label>
                        <div class="w-full sm:w-72">
                            <div class="relative">
                                <input id="new_password" type="password" name="new_password" required
                                    class="password-input w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-11
               focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">

                                <button type="button"
                                    class="password-toggle absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i data-feather="eye" class="w-5 h-5"></i>
                                </button>
                            </div>

                            {{-- Password Rules --}}
                            <div id="passwordRules" class="mt-3 text-sm text-gray-600 space-y-1.5">
                                @foreach ([
            'length' => 'At least 8 characters',
            'uppercase' => 'Contains uppercase letter (A-Z)',
            'lowercase' => 'Contains lowercase letter (a-z)',
            'number' => 'Contains a number (0-9)',
            'special' => 'Contains special character (@$!%*?&)',
        ] as $rule => $label)
                                    <div data-rule="{{ $rule }}" class="flex items-center gap-2">
                                        <span
                                            class="rule-icon w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center text-xs text-gray-400">•</span>
                                        <span>{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <label class="text-sm font-medium text-gray-600 w-44">
                            Confirm New Password
                        </label>

                        <div class="relative w-full sm:w-72">
                            <input type="password" name="new_password_confirmation" required
                                class="password-input w-full rounded-xl border border-gray-300
                      px-4 py-2.5 pr-11
                      focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition">

                            <button type="button"
                                class="password-toggle absolute inset-y-0 right-3
                   flex items-center text-gray-400 hover:text-gray-600">
                                <i data-feather="eye" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>


                    <div class="flex justify-end pt-4">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-blue-700 text-sm font-medium
                           shadow-sm hover:bg-blue-200 hover:shadow-md transition-all duration-150">
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

    <script>
        (function() {
            const pw = document.getElementById('new_password');
            if (!pw) return;

            const rules = {
                length: v => v.length >= 8,
                uppercase: v => /[A-Z]/.test(v),
                lowercase: v => /[a-z]/.test(v),
                number: v => /\d/.test(v),
                special: v => /[@$!%*?&]/.test(v),
            };

            const ruleEls = {};
            document.querySelectorAll('#passwordRules [data-rule]').forEach(el => {
                ruleEls[el.dataset.rule] = el;
            });

            function updateIcons() {
                const val = pw.value || '';
                Object.keys(rules).forEach(key => {
                    const ok = rules[key](val);
                    const icon = ruleEls[key].querySelector('.rule-icon');

                    if (ok) {
                        icon.className =
                            'rule-icon w-5 h-5 rounded-full flex items-center justify-center text-green-700 bg-green-100 border border-green-300';
                        icon.innerHTML =
                            "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor' class='w-3 h-3'><path fill-rule='evenodd' d='M16.707 5.293a1 1 0 00-1.414-1.414L7 12.172l-2.293-2.293A1 1 0 003.293 11.293l3 3a1 1 0 001.414 0l9-9z' clip-rule='evenodd'/></svg>";
                    } else {
                        icon.className =
                            'rule-icon w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center text-xs text-gray-400';
                        icon.textContent = '•';
                    }
                });
            }

            pw.addEventListener('input', updateIcons);
            updateIcons();
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();

            document.querySelectorAll('.password-toggle').forEach(btn => {
                btn.addEventListener('click', () => {
                    const wrapper = btn.closest('.relative');
                    const input = wrapper.querySelector('.password-input');
                    const icon = btn.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.setAttribute('data-feather', 'eye-off');
                    } else {
                        input.type = 'password';
                        icon.setAttribute('data-feather', 'eye');
                    }

                    feather.replace();
                });
            });
        });
    </script>

@endsection
