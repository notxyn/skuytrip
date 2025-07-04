@extends('layouts.app')

@section('content')
<div class="relative bg-cover bg-center min-h-screen" style="background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80');">
    <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-black/40 to-black/70"></div>
    <div class="relative z-10 min-h-screen flex flex-col">
        <!-- Hero Section -->
        <div class="flex flex-col md:flex-row items-center justify-between flex-1 px-10 py-16 gap-10">
            <div class="max-w-xl">
                <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 leading-tight drop-shadow-lg">Welcome to <br><span class="text-orange-400">Kepulauan Riau</span></h1>
                <p class="text-2xl text-white/90 mb-10 font-light">Adventure and Tranquility</p>
                <div class="flex flex-wrap gap-3 mb-8">
                    <span class="bg-white/20 text-white px-4 py-1 rounded-full text-sm font-medium">LANDSCAPE</span>
                    <span class="bg-white/20 text-white px-4 py-1 rounded-full text-sm font-medium">EXCLUSIVE</span>
                    <span class="bg-white/20 text-white px-4 py-1 rounded-full text-sm font-medium">JOURNEY</span>
                    <span class="bg-white/20 text-white px-4 py-1 rounded-full text-sm font-medium">EXCITING</span>
                    <span class="bg-white/20 text-white px-4 py-1 rounded-full text-sm font-medium">TRAVEL</span>
                </div>
            </div>
            <!-- Search Box -->
            <form action="/destination" method="GET" class="bg-white/95 rounded-2xl shadow-2xl p-8 w-full max-w-sm mt-8 md:mt-0 flex flex-col gap-4">
                <h2 class="text-xl font-bold mb-1 text-gray-800">Discover Place</h2>
                <input type="text" name="q" placeholder="Place Name" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-2 outline-none focus:outline-none transition">
                <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-lg font-semibold shadow hover:bg-orange-600 transition">Search Place</button>
            </form>
        </div>
    </div>
</div>
<!-- Destinations Section -->
<div class="max-w-7xl mx-auto px-4 py-16">
    <h2 class="text-3xl font-extrabold mb-8 text-gray-800">An Unmissable Tourist Attraction</h2>
    <div class="flex flex-wrap gap-8 mb-12 justify-center">
        @foreach($attractions as $attraction)
            <a href="{{ route('destination.detail', ['slug' => $attraction->slug]) }}" class="w-48 h-32 bg-gray-200 rounded-xl overflow-hidden shadow-lg hover:scale-105 transition block">
                <img src="{{ $attraction->img ? (Str::startsWith($attraction->img, ['http://', 'https://']) ? $attraction->img : asset('storage/' . $attraction->img)) : 'https://via.placeholder.com/400x200?text=No+Image' }}" alt="{{ $attraction->name }}" class="w-full h-full object-cover">
                <div class="text-center text-sm font-semibold text-gray-700 bg-white/80 py-1">{{ $attraction->name }}</div>
            </a>
        @endforeach
    </div>
    <!-- Recommendations Section -->
    @if($recommendations->isNotEmpty())
        <div class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-orange-600">Recommended for You</h2>
            <div class="flex flex-wrap gap-6 justify-center">
                @foreach($recommendations as $rec)
                    <a href="{{ route('destination.detail', ['slug' => $rec->slug]) }}" class="w-44 h-28 bg-orange-100 rounded-xl overflow-hidden shadow hover:scale-105 transition block">
                        <img src="{{ $rec->img ? (Str::startsWith($rec->img, ['http://', 'https://']) ? $rec->img : asset('storage/' . $rec->img)) : 'https://via.placeholder.com/400x200?text=No+Image' }}" alt="{{ $rec->name }}" class="w-full h-full object-cover">
                        <div class="text-center text-xs font-bold text-orange-700 bg-white/80 py-1">{{ $rec->name }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
    <div class="flex flex-col md:flex-row gap-12">
        <div class="flex-1">
            <p class="text-gray-700 mb-6 text-lg italic">“Tikus Beach in Bintan stole my heart. The serenity, the waves, the coconut sands, and the warm sun made every moment magical. A must-visit for anyone seeking paradise on earth!”</p>
            <div class="flex items-center space-x-2 mb-2">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-10 h-10 rounded-full border-2 border-white shadow" />
                <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-10 h-10 rounded-full border-2 border-white shadow -ml-4" />
                <img src="https://randomuser.me/api/portraits/men/45.jpg" class="w-10 h-10 rounded-full border-2 border-white shadow -ml-4" />
                <span class="text-base text-gray-600 ml-2 font-medium">100K happy guests</span>
            </div>
            <div class="flex items-center text-yellow-400 mb-2 text-lg">
                <span>★ ★ ★ ★ ★</span>
                <span class="text-gray-600 text-base ml-2">18,927 reviews</span>
            </div>
        </div>
        <div class="flex-1 grid grid-cols-2 gap-6">
            @foreach($attractions->take(4) as $attraction)
                <a href="{{ route('destination.detail', ['slug' => $attraction->slug]) }}" class="bg-white rounded-xl overflow-hidden shadow-lg hover:scale-105 transition block">
                    <img src="{{ $attraction->img ? (Str::startsWith($attraction->img, ['http://', 'https://']) ? $attraction->img : asset('storage/' . $attraction->img)) : 'https://via.placeholder.com/400x200?text=No+Image' }}" alt="{{ $attraction->name }}" class="w-full h-32 object-cover">
                    <div class="p-3 text-base font-semibold text-gray-700">{{ $attraction->name }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>
<!-- Popular Beach Section -->
<div class="bg-gradient-to-br from-orange-50 to-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-extrabold mb-8 text-gray-800">A Popular Tourist Hotspot Beach</h2>
        <div class="flex flex-col md:flex-row gap-10">
            @if($featured)
            <div class="flex-1 bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center hover:shadow-2xl transition">
                <img src="{{ $featured->img ? (Str::startsWith($featured->img, ['http://', 'https://']) ? $featured->img : asset('storage/' . $featured->img)) : 'https://via.placeholder.com/400x400?text=No+Image' }}" class="w-44 h-44 object-cover rounded-xl mb-6 shadow" alt="{{ $featured->name }}">
                <h3 class="text-2xl font-bold mb-2 text-gray-800">{{ $featured->name }}</h3>
                <p class="text-gray-600 mb-6 text-center">{{ $featured->desc }}</p>
                <a href="{{ route('destination.detail', ['slug' => $featured->slug]) }}" class="bg-orange-500 text-white px-8 py-3 rounded-lg font-semibold shadow hover:bg-orange-600 transition">Get Started</a>
            </div>
            @endif
            <!-- Optionally, show more popular attractions here -->
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="bg-white py-10 mt-16 border-t shadow-inner">
    <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="mb-4 md:mb-0">
            <h4 class="text-2xl font-bold mb-1">Connect With Us</h4>
            <p class="text-gray-600 text-base">Best hotels & tours in one click only in Skuytrips</p>
        </div>
        <div class="flex space-x-6">
            <a href="#" class="text-gray-500 hover:text-orange-500 text-2xl transition"><i class="fab fa-facebook"></i></a>
            <a href="#" class="text-gray-500 hover:text-orange-500 text-2xl transition"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-gray-500 hover:text-orange-500 text-2xl transition"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
    <div class="text-center text-gray-400 text-sm mt-6">© 2024 Copyright by SkuyTrips</div>
</footer>
@endsection 