<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SkuyTrips</title>
        @vite('resources/css/app.css')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body class="bg-gray-50 text-gray-800">
        <!-- Global Session Status Notification -->
        @if(session('status'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 1500)" class="fixed top-24 left-0 right-0 flex justify-center z-50">
                <div class="bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg text-lg font-semibold transition-all duration-500" x-transition:leave="ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="min-width: 220px; text-align: center;">
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-4">
                        <a href="/" class="text-2xl font-bold text-orange-500">SkuyTrips</a>
                    </div>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center gap-6">
                        <a href="/" class="text-gray-600 hover:text-orange-500 transition">Home</a>
                        <a href="/destination" class="text-gray-600 hover:text-orange-500 transition">Destination</a>
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <div x-data="{ open: false, timeout: null }" @mouseenter="clearTimeout(timeout); open = true" @mouseleave="timeout = setTimeout(() => { open = false }, 500)" class="relative hidden md:block">
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
                            <div class="hidden md:flex items-center gap-4">
                                <a href="{{ route('login') }}" class="text-gray-600 hover:text-orange-500 transition">Login</a>
                                <a href="{{ route('register') }}" class="bg-orange-500 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-orange-600 transition">Register</a>
                            </div>
                        @endauth

                        <!-- Mobile menu button -->
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div x-show="mobileMenuOpen" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="md:hidden border-t border-gray-200 py-4">
                    <div class="flex flex-col space-y-4">
                        <a href="/" class="text-gray-600 hover:text-orange-500 transition py-2">Home</a>
                        <a href="/destination" class="text-gray-600 hover:text-orange-500 transition py-2">Destination</a>
                        
                        @auth
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-center gap-3 mb-4">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="w-10 h-10 rounded-full object-cover">
                                    <span class="font-semibold">{{ Auth::user()->name }}</span>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block py-2 text-gray-700 hover:text-orange-500">Profile</a>
                                <a href="{{ route('user.settings') }}" class="block py-2 text-gray-700 hover:text-orange-500">Purchase History</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left py-2 text-gray-700 hover:text-orange-500">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="border-t border-gray-200 pt-4 flex flex-col space-y-2">
                                <a href="{{ route('login') }}" class="text-gray-600 hover:text-orange-500 transition py-2">Login</a>
                                <a href="{{ route('register') }}" class="bg-orange-500 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-orange-600 transition text-center">Register</a>
                            </div>
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
