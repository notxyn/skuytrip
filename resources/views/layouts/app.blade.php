<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Skuy-Trips</title>
        @vite('resources/css/app.css')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body class="bg-gray-50 text-gray-800">
        <nav class="bg-white shadow-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-4">
                        <a href="/" class="text-2xl font-bold text-orange-500">Skuy-Trips</a>
                    </div>
                    <div class="hidden md:flex items-center gap-6">
                        <a href="/" class="text-gray-600 hover:text-orange-500 transition">Home</a>
                        <a href="/destination" class="text-gray-600 hover:text-orange-500 transition">Destination</a>
                        <a href="#" class="text-gray-600 hover:text-orange-500 transition">About Us</a>
                        <a href="#" class="text-gray-600 hover:text-orange-500 transition">Contact</a>
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <div x-data="{ open: false, timeout: null }" @mouseenter="clearTimeout(timeout); open = true" @mouseleave="timeout = setTimeout(() => { open = false }, 500)" class="relative">
                                <button class="flex items-center gap-2">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="w-10 h-10 rounded-full object-cover">
                                    <span class="font-semibold">{{ Auth::user()->name }}</span>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </button>
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform scale-95"
                                     x-transition:enter-end="opacity-100 transform scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 transform scale-100"
                                     x-transition:leave-end="opacity-0 transform scale-95"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50 origin-top-right">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50">Profile</a>
                                    <a href="{{ route('user.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50">Purchase History</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-orange-500 transition">Login</a>
                            <a href="{{ route('register') }}" class="bg-orange-500 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-orange-600 transition">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
    </body>
</html>
