@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 flex justify-center py-12">
    <div class="flex w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-56 bg-orange-50 border-r border-orange-100 flex flex-col py-8 px-4">
            <div class="mb-8 flex flex-col items-center">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-16 h-16 rounded-full object-cover border-4 border-orange-200 mb-2">
                <div class="font-semibold text-lg">{{ auth()->user()->name }}</div>
            </div>
            <nav class="flex flex-col gap-2">
                <a href="?tab=history" class="px-4 py-2 rounded-lg font-medium text-left transition bg-orange-500 text-white">Purchase History</a>
            </nav>
        </aside>
        <!-- Main Content -->
        <div class="flex-1 p-10">
            <h2 class="text-2xl font-bold mb-8 text-orange-500">Purchase History</h2>
            @if($bookings->count())
                <div class="space-y-6">
                    @foreach($bookings as $booking)
                        <div class="bg-white border border-orange-100 rounded-xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow">
                            <div class="flex items-center gap-4">
                                <img src="{{ $booking->attraction->img ?? 'https://via.placeholder.com/80x80?text=No+Image' }}" class="w-20 h-20 object-cover rounded-lg border">
                                <div>
                                    <div class="font-bold text-lg text-orange-500">{{ $booking->attraction->name }}</div>
                                    <div class="text-gray-500 text-sm mb-1">{{ $booking->attraction->loc }}</div>
                                    <div class="text-gray-400 text-xs">Booked on {{ $booking->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <div class="font-semibold">{{ $booking->date }} &bull; {{ $booking->quantity }} ticket(s)</div>
                                <div class="text-gray-600">Payment: {{ ucfirst($booking->payment_method) }}</div>
                                <div class="font-bold text-lg text-orange-600 mt-1">Rp{{ number_format($booking->total,0,',','.') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-orange-50 border border-orange-100 rounded-xl p-8 text-center text-gray-500">
                    <i class="fas fa-receipt text-4xl mb-4 text-orange-300"></i>
                    <div class="text-lg font-semibold mb-2">You have no purchases yet.</div>
                    <div>All your bookings and order history will appear here.</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 