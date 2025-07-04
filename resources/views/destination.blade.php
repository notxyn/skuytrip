@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Search Bar -->
    <div class="flex justify-center pt-12 pb-6 bg-gradient-to-b from-orange-50 to-transparent">
        <form action="/destination" method="GET" class="w-full max-w-xl flex items-center bg-white rounded-full shadow-lg border border-orange-100 px-4 py-2 gap-2">
            <span class="text-orange-400 text-xl"><i class="fas fa-search"></i></span>
            <input type="text" name="q" style="border: none !important; box-shadow: none !important;" class="flex-1 px-3 py-2 text-lg bg-transparent outline-none shadow-none focus:outline-none focus:border-none" placeholder="Search attractions, tags, or locations..." value="{{ request('q') }}">
            <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded-full font-semibold shadow hover:bg-orange-600 transition">Search</button>
        </form>
    </div>
    <!-- Main Content -->
    <div class="flex-1 w-full max-w-7xl mx-auto px-4 py-10">
        <h2 class="text-3xl font-extrabold mb-10 text-gray-800 text-center">All Attractions</h2>
        @if($attractions->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach ($attractions as $attraction)
                <a href="/destination/{{ $attraction->slug }}" class="group bg-white rounded-3xl shadow-md border border-orange-100 flex flex-col gap-2 hover:shadow-2xl hover:-translate-y-1 transition-all duration-200 no-underline overflow-hidden">
                    <div class="w-full h-44 bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if($attraction->img)
                            <img src="{{ $attraction->img ? (Str::startsWith($attraction->img, ['http://', 'https://']) ? $attraction->img : asset('storage/' . $attraction->img)) : asset('images/placeholder.png') }}" alt="{{ $attraction->name }}" class="w-full h-40 object-cover rounded-t-xl" />
                        @else
                            <span class="text-gray-400 text-5xl"><i class="fas fa-image"></i></span>
                        @endif
                    </div>
                    <div class="px-6 pt-4 pb-6 flex-1 flex flex-col">
                        <div class="font-bold text-lg text-gray-900 mb-1">{{ $attraction->name }}</div>
                        <div class="flex items-center text-orange-400 text-base font-semibold mb-1"><span class="mr-1">★</span>{{ $attraction->rate }}</div>
                        <div class="text-gray-500 text-sm mb-2">{{ $attraction->loc }}</div>
                        <div class="text-gray-600 text-sm mb-3 flex-1">{{ Str::limit($attraction->desc, 80) }}</div>
                        <div class="font-bold text-lg text-orange-500 mb-2">{{ $attraction->price }} <span class="text-xs font-normal text-gray-500">Per person</span></div>
                        <div class="flex flex-wrap gap-2 mt-auto">
                            @php
                                $tags = is_array($attraction->tags) ? $attraction->tags : explode(',', $attraction->tags);
                            @endphp
                            @foreach ($tags as $tag)
                                <span class="bg-orange-100 px-3 py-1 rounded-full text-xs font-bold text-orange-700">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-12 flex justify-center">
            {{ $attractions->links() }}
        </div>
        @elseif(request('q'))
            <div class="text-center text-gray-500 text-lg my-12">No results found for "<span class="font-semibold">{{ request('q') }}</span>".</div>
            @if(isset($suggestions) && $suggestions->count())
                <div class="text-center text-orange-500 font-semibold mb-6">Did you mean...</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    @foreach ($suggestions as $attraction)
                        <a href="/destination/{{ $attraction->slug }}" class="group bg-white rounded-3xl shadow-md border border-orange-100 flex flex-col gap-2 hover:shadow-2xl hover:-translate-y-1 transition-all duration-200 no-underline overflow-hidden">
                            <div class="w-full h-44 bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($attraction->img)
                                    <img src="{{ $attraction->img ? (Str::startsWith($attraction->img, ['http://', 'https://']) ? $attraction->img : asset('storage/' . $attraction->img)) : asset('images/placeholder.png') }}" alt="{{ $attraction->name }}" class="w-full h-40 object-cover rounded-t-xl" />
                                @else
                                    <span class="text-gray-400 text-5xl"><i class="fas fa-image"></i></span>
                                @endif
                            </div>
                            <div class="px-6 pt-4 pb-6 flex-1 flex flex-col">
                                <div class="font-bold text-lg text-gray-900 mb-1">{{ $attraction->name }}</div>
                                <div class="flex items-center text-orange-400 text-base font-semibold mb-1"><span class="mr-1">★</span>{{ $attraction->rate }}</div>
                                <div class="text-gray-500 text-sm mb-2">{{ $attraction->loc }}</div>
                                <div class="text-gray-600 text-sm mb-3 flex-1">{{ Str::limit($attraction->desc, 80) }}</div>
                                <div class="font-bold text-lg text-orange-500 mb-2">{{ $attraction->price }} <span class="text-xs font-normal text-gray-500">Per person</span></div>
                                <div class="flex flex-wrap gap-2 mt-auto">
                                    @php
                                        $tags = is_array($attraction->tags) ? $attraction->tags : explode(',', $attraction->tags);
                                    @endphp
                                    @foreach ($tags as $tag)
                                        <span class="bg-orange-100 px-3 py-1 rounded-full text-xs font-bold text-orange-700">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
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