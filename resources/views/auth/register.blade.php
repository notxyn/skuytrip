@extends('layouts.app')

@section('content')
<style>
    body {
        background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') center center/cover no-repeat fixed;
    }
    .input-group {
        position: relative;
    }
    .input-group input[type="password"],
    .input-group input[type="text"] {
        padding-right: 2.5rem !important;
    }
    .input-group .toggle-password {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #a3a3a3;
        background: transparent;
        border: none;
        outline: none;
    }
    .input-group .toggle-password:focus svg,
    .input-group .toggle-password:hover svg {
        color: #f97316;
    }
</style>
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8">
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-extrabold text-orange-500 mb-2">Create Account</h1>
            <p class="text-gray-500">Sign up for Skuy-Trips and start your journey!</p>
        </div>
        
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-400 @error('name') border-red-500 @enderror">
                @error('name')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-400 @error('email') border-red-500 @enderror">
                @error('email')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-400 @error('password') border-red-500 @enderror">
                    <button type="button" tabindex="-1" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password';this.querySelector('svg').classList.toggle('text-orange-500');" class="toggle-password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                </div>
                @error('password')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="input-group">
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-400 @error('password_confirmation') border-red-500 @enderror">
                    <button type="button" tabindex="-1" onclick="const p=document.getElementById('password_confirmation');p.type=p.type==='password'?'text':'password';this.querySelector('svg').classList.toggle('text-orange-500');" class="toggle-password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-lg font-bold text-lg shadow hover:bg-orange-600 transition">Register</button>
        </form>
        <div class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="text-orange-500 font-semibold hover:underline">Log in</a>
        </div>
    </div>
</div>
@endsection
