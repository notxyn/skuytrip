@extends('layouts.app')
@section('content')
<div class="flex flex-col min-h-screen bg-gray-50">
    <!-- Search Bar -->
    <div class="flex justify-center pt-12 pb-6 bg-gradient-to-b from-orange-50 to-transparent">
        <form action="/destination" method="GET" class="flex gap-2 items-center px-4 py-2 w-full max-w-xl bg-white rounded-full border border-orange-100 shadow-lg">
            <span class="text-xl text-orange-400"><i class="fas fa-search"></i></span>
            <input type="text" name="q" style="border: none !important; box-shadow: none !important;" class="flex-1 px-3 py-2 text-lg bg-transparent shadow-none outline-none focus:outline-none focus:border-none" placeholder="Search attractions, tags, or locations..." value="{{ request('q') }}">
            <button type="submit" class="px-6 py-2 font-semibold text-white bg-orange-500 rounded-full shadow transition hover:bg-orange-600">Search</button>
        </form>
    </div>
    <!-- Main Content -->
    <div class="flex-1 px-4 py-10 mx-auto w-full max-w-7xl">
        <h2 class="mb-10 text-3xl font-extrabold text-center text-gray-800">All Attractions</h2>
        @if($attractions->count() > 0)
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($attractions as $attraction)
                <a href="/destination/{{ $attraction->slug }}" class="flex overflow-hidden flex-col gap-2 no-underline bg-white rounded-3xl border border-orange-100 shadow-md transition-all duration-200 group hover:shadow-2xl hover:-translate-y-1">
                    <div class="flex overflow-hidden justify-center items-center w-full h-44 bg-gray-100">
                        @if($attraction->img)
                            <img src="{{ $attraction->img ? (Str::startsWith($attraction->img, ['http://', 'https://']) ? $attraction->img : asset('storage/' . $attraction->img)) : asset('images/placeholder.png') }}" alt="{{ $attraction->name }}" class="object-cover w-full h-40 rounded-t-xl" />
                        @else
                            <span class="text-5xl text-gray-400"><i class="fas fa-image"></i></span>
                        @endif
                    </div>
                    <div class="flex flex-col flex-1 px-6 pt-4 pb-6">
                        <div class="mb-1 text-lg font-bold text-gray-900">{{ $attraction->name }}</div>
                        <div class="flex items-center mb-1 text-base font-semibold text-orange-400"><span class="mr-1">★</span>{{ $attraction->rate }}</div>
                        <div class="mb-2 text-sm text-gray-500">{{ $attraction->loc }}</div>
                        <div class="flex-1 mb-3 text-sm text-gray-600">{{ Str::limit($attraction->desc, 80) }}</div>
                        <div class="mb-2 text-lg font-bold text-orange-500">{{ $attraction->price }} <span class="text-xs font-normal text-gray-500">Per person</span></div>
                        <div class="flex flex-wrap gap-2 mt-auto">
                            @php
                                $tags = is_array($attraction->tags) ? $attraction->tags : explode(',', $attraction->tags);
                            @endphp
                            @foreach ($tags as $tag)
                                <span class="px-3 py-1 text-xs font-bold text-orange-700 bg-orange-100 rounded-full">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="flex justify-center mt-12">
            {{ $attractions->links() }}
        </div>
        @elseif(request('q'))
            <div class="my-12 text-lg text-center text-gray-500">No results found for "<span class="font-semibold">{{ request('q') }}</span>".</div>
            @if(isset($suggestions) && $suggestions->count())
                <div class="mb-6 font-semibold text-center text-orange-500">Did you mean...</div>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($suggestions as $attraction)
                        <a href="/destination/{{ $attraction->slug }}" class="flex overflow-hidden flex-col gap-2 no-underline bg-white rounded-3xl border border-orange-100 shadow-md transition-all duration-200 group hover:shadow-2xl hover:-translate-y-1">
                            <div class="flex overflow-hidden justify-center items-center w-full h-44 bg-gray-100">
                                @if($attraction->img)
                                    <img src="{{ $attraction->img ? (Str::startsWith($attraction->img, ['http://', 'https://']) ? $attraction->img : asset('storage/' . $attraction->img)) : asset('images/placeholder.png') }}" alt="{{ $attraction->name }}" class="object-cover w-full h-40 rounded-t-xl" />
                                @else
                                    <span class="text-5xl text-gray-400"><i class="fas fa-image"></i></span>
                                @endif
                            </div>
                            <div class="flex flex-col flex-1 px-6 pt-4 pb-6">
                                <div class="mb-1 text-lg font-bold text-gray-900">{{ $attraction->name }}</div>
                                <div class="flex items-center mb-1 text-base font-semibold text-orange-400"><span class="mr-1">★</span>{{ $attraction->rate }}</div>
                                <div class="mb-2 text-sm text-gray-500">{{ $attraction->loc }}</div>
                                <div class="flex-1 mb-3 text-sm text-gray-600">{{ Str::limit($attraction->desc, 80) }}</div>
                                <div class="mb-2 text-lg font-bold text-orange-500">{{ $attraction->price }} <span class="text-xs font-normal text-gray-500">Per person</span></div>
                                <div class="flex flex-wrap gap-2 mt-auto">
                                    @php
                                        $tags = is_array($attraction->tags) ? $attraction->tags : explode(',', $attraction->tags);
                                    @endphp
                                    @foreach ($tags as $tag)
                                        <span class="px-3 py-1 text-xs font-bold text-orange-700 bg-orange-100 rounded-full">{{ $tag }}</span>
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
    <footer class="py-12 mt-16 bg-gray-100">
        <div class="flex flex-col gap-8 justify-between items-start px-4 mx-auto max-w-7xl md:flex-row md:items-center">
            <div>
                <h4 class="mb-2 text-3xl font-bold">Connect<br>With Us</h4>
                <p class="mb-2 text-base text-gray-600">Best Hotels & Tours in One Click! only in Skuytrips</p>
            </div>
            <div class="flex flex-col gap-2 items-end">
                <div class="mb-1 text-lg font-semibold">Menu</div>
                <a href="/destination" class="text-gray-700 hover:text-orange-500">Destination</a>
            </div>
        </div>
        <div class="flex justify-between items-center px-4 mx-auto mt-8 max-w-7xl">
            <div class="text-xs text-gray-400">© 2024 Copyright By SkuyTrips</div>
            <div class="flex space-x-3">
                <a href="#" class="p-2 bg-white rounded-full shadow hover:bg-gray-200"><i class="fab fa-youtube"></i></a>
                <a href="#" class="p-2 bg-white rounded-full shadow hover:bg-gray-200"><i class="fab fa-tiktok"></i></a>
                <a href="#" class="p-2 bg-white rounded-full shadow hover:bg-gray-200"><i class="fab fa-instagram"></i></a>
                <a href="#" class="p-2 bg-white rounded-full shadow hover:bg-gray-200"><i class="fab fa-facebook"></i></a>
            </div>
        </div>
        <div class="absolute left-0 right-0 bottom-0 text-[10rem] text-gray-200 font-extrabold opacity-20 select-none pointer-events-none text-center -z-10">INDONESIA</div>
    </footer>
</div>
@endsection 