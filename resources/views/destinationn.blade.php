@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative w-full h-80 mb-12">
        <img src="{{ $attraction->img ?: 'https://via.placeholder.com/1200x320?text=No+Image' }}" class="w-full h-80 object-cover rounded-b-3xl">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent rounded-b-3xl"></div>
        <div class="absolute left-0 bottom-0 p-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-white drop-shadow-lg">{{ $attraction->name }}</h1>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row gap-10">
            <!-- Overview -->
            <div class="flex-1">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-2 text-gray-900">Overview</h2>
                    <p class="mb-4 text-gray-700 leading-relaxed">{{ $attraction->desc }}</p>
                    <div class="flex gap-2 mb-6 flex-wrap">
                        @php
                            $tags = is_array($attraction->tags) ? $attraction->tags : explode(',', $attraction->tags);
                        @endphp
                        @foreach ($tags as $tag)
                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- Booking Form -->
            <div class="w-full md:w-96">
                <div class="bg-white border border-orange-200 rounded-2xl shadow-xl p-8 sticky top-24">
                    <h3 class="font-bold text-xl mb-4 flex items-center text-orange-500"><svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.104.896-2 2-2s2 .896 2 2-.896 2-2 2-2-.896-2-2zm0 0V7m0 4v4m0 0h4m-4 0H8" /></svg>Booking</h3>
                    @auth
                    <form class="space-y-4" action="/checkout/{{ $attraction->slug }}" method="GET">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Name</label>
                            <input type="text" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-400" value="{{ auth()->user()->name }}" readonly>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Email</label>
                            <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-400" value="{{ auth()->user()->email }}" readonly>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Ticket Quantity</label>
                            <input type="number" min="1" name="quantity" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-400" placeholder="Ticket Quantity" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Active Phone Number</label>
                            <input type="text" name="phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-400" placeholder="Place Phone Number" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Visit Date</label>
                            <input type="date" name="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-400" required>
                        </div>
                        <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-lg font-bold text-lg shadow hover:bg-orange-600 transition">Booking Now</button>
                    </form>
                    @else
                    <div class="text-center py-8">
                        <div class="text-orange-500 font-bold text-lg mb-4">Please login or register to book.</div>
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="w-full bg-orange-500 text-white py-3 rounded-lg font-bold text-lg shadow hover:bg-orange-600 transition">Login</a>
                            <a href="{{ route('register', ['redirect' => url()->current()]) }}" class="w-full bg-orange-100 text-orange-600 py-3 rounded-lg font-bold text-lg shadow hover:bg-orange-200 transition">Register</a>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
        <!-- Customer Reviews -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-8 text-gray-800">Customers Are Happy With Our Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white border border-gray-200 rounded-2xl shadow p-6 w-80 flex-shrink-0 hover:shadow-lg transition">
                    <div class="flex items-center mb-2">
                        <span class="text-orange-400 text-xl mr-1">★★★★★</span>
                    </div>
                    <p class="text-gray-700 mb-4 text-sm">Super convenient! I just picked the attraction, paid online, and got the e-ticket instantly. No more waiting in long lines. Highly recommended!</p>
                    <div class="flex items-center gap-2 mt-2">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-8 h-8 rounded-full">
                        <span class="text-gray-800 text-sm font-semibold">Intan</span>
                        <span class="text-gray-500 text-xs">Indonesia</span>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl shadow p-6 w-80 flex-shrink-0 hover:shadow-lg transition">
                    <div class="flex items-center mb-2">
                        <span class="text-orange-400 text-xl mr-1">★★★★★</span>
                    </div>
                    <p class="text-gray-700 mb-4 text-sm">User-friendly website, great deals, and a wide range of attractions to choose from. I didn't expect booking tickets could be this easy!</p>
                    <div class="flex items-center gap-2 mt-2">
                        <img src="https://randomuser.me/api/portraits/women/65.jpg" class="w-8 h-8 rounded-full">
                        <span class="text-gray-800 text-sm font-semibold">Sarah Lopez</span>
                        <span class="text-gray-500 text-xs">Singapore</span>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl shadow p-6 w-80 flex-shrink-0 hover:shadow-lg transition">
                    <div class="flex items-center mb-2">
                        <span class="text-orange-400 text-xl mr-1">★★★★★</span>
                    </div>
                    <p class="text-gray-700 mb-4 text-sm">Everything went smoothly from booking to check-in. The ticket was accepted without any issues, and the whole experience was seamless. Will definitely use this service again!</p>
                    <div class="flex items-center gap-2 mt-2">
                        <img src="https://randomuser.me/api/portraits/men/45.jpg" class="w-8 h-8 rounded-full">
                        <span class="text-gray-800 text-sm font-semibold">adit</span>
                        <span class="text-gray-500 text-xs">Indonesia</span>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($recommendedAttractions) && $recommendedAttractions->count())
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-8 text-orange-500">You might also like</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recommendedAttractions as $rec)
                    <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
                        <img src="{{ $rec->img ?? 'https://via.placeholder.com/120x120?text=No+Image' }}" class="w-28 h-28 object-cover rounded-lg mb-3">
                        <div class="font-bold text-lg text-orange-600 mb-1">{{ $rec->name }}</div>
                        <div class="text-gray-500 text-sm mb-2">{{ $rec->loc }}</div>
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach($rec->tags as $tag)
                                <span class="bg-orange-100 text-orange-600 px-2 py-1 rounded text-xs">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <a href="/destination/{{ $rec->slug }}" class="mt-auto px-4 py-2 bg-orange-500 text-white rounded-lg font-semibold text-sm shadow hover:bg-orange-600 transition">View</a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        <!-- Related Destinations (optional: you can implement this later) -->
        @if(isset($attraction['related']) && is_array($attraction['related']) && count($attraction['related']))
        <div class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-10">
            @foreach($attraction['related'] as $rel)
            <div class="relative rounded-3xl overflow-hidden shadow-lg group">
                <img src="{{ $rel['img'] }}" class="w-full h-72 object-cover group-hover:scale-105 transition">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute left-0 bottom-0 p-8 w-full">
                    <div class="flex gap-2 mb-2">
                        @foreach($rel['tags'] as $tag)
                        <span class="bg-white/40 text-white px-3 py-1 rounded-full text-xs font-semibold">{{ $tag }}</span>
                        @endforeach
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">{{ $rel['name'] }}</h3>
                    <p class="text-white/90 text-base mb-4 max-w-md">{{ $rel['desc'] }}</p>
                    <a href="#" class="inline-flex items-center px-6 py-2 bg-white/90 rounded-full font-semibold text-gray-900 hover:bg-orange-500 hover:text-white transition">View More <span class="ml-2">&rarr;</span></a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    <!-- Footer -->
    <footer class="bg-gray-100 mt-16 py-12">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
            <div>
                <h4 class="text-3xl font-bold mb-2">Connect<br>With Us</h4>
                <p class="text-gray-600 text-base mb-2">Best Hotels & Tours in One Click! only in Skuytrips</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <div class="font-semibold text-lg mb-1">Menu</div>
                <a href="/destination" class="text-gray-700 hover:text-orange-500">Destination</a>
            </div>
        </div>
        <div class="flex justify-between items-center max-w-7xl mx-auto px-4 mt-8">
            <div class="text-gray-400 text-xs">© 2024 Copyright By SkuyTrips</div>
            <div class="flex space-x-3">
                <a href="#" class="bg-white rounded-full p-2 shadow hover:bg-gray-200"><i class="fab fa-youtube"></i></a>
                <a href="#" class="bg-white rounded-full p-2 shadow hover:bg-gray-200"><i class="fab fa-tiktok"></i></a>
                <a href="#" class="bg-white rounded-full p-2 shadow hover:bg-gray-200"><i class="fab fa-instagram"></i></a>
                <a href="#" class="bg-white rounded-full p-2 shadow hover:bg-gray-200"><i class="fab fa-facebook"></i></a>
            </div>
        </div>
        <div class="absolute left-0 right-0 bottom-0 text-[10rem] text-gray-200 font-extrabold opacity-20 select-none pointer-events-none text-center -z-10">INDONESIA</div>
    </footer>
</div>
@endsection 