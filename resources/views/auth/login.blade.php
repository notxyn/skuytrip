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
<div class="min-h-screen flex flex-col justify-center items-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-10 border border-orange-100" style="backdrop-filter: blur(2px);">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-2 text-center">Login</h2>
        <div class="text-gray-500 text-base font-light mb-8 text-center">Login to access your SkuyTrips account</div>
        
        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full px-4 py-3 rounded-md border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition placeholder-gray-400" placeholder="john.doe@gmail.com" />
                @error('email')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full px-4 py-3 rounded-md border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition placeholder-gray-400" placeholder="Your password" />
                    <button type="button" tabindex="-1" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password';this.querySelector('svg').classList.toggle('text-orange-500');" class="toggle-password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                </div>
                @error('password')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mt-4 flex items-center gap-2">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                <label for="remember_me" class="text-sm text-gray-600 select-none">Remember me</label>
            </div>
            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-md font-bold text-lg shadow transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-orange-300 mt-2">Login</button>
        </form>
        @if (Route::has('register'))
        <div class="text-center text-gray-500 text-sm mt-6">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-orange-500 font-semibold hover:underline">Sign up</a>
        </div>
        @endif
    </div>
</div>
<script>
// Optional: autofocus email
if(document.getElementById('email')) document.getElementById('email').focus();
</script>
@endsection
