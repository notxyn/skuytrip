@extends('layouts.app')
@section('content')
<div class="flex flex-col justify-center items-center py-12 min-h-screen bg-gray-50">
    <div id="checkout-content" @if(session('success')) style="display:none" @endif>
        <div class="flex flex-col gap-8 mb-10 w-full max-w-5xl md:flex-row">
            <!-- Booking Details -->
            <div class="flex-1 min-w-[260px] bg-white rounded-2xl shadow-xl p-8">
                <h2 class="mb-6 text-xl font-bold">Booking</h2>
                <div class="space-y-2 text-gray-700">
                    <div><span class="font-semibold">Name</span><br>{{ $booking['name'] }}</div>
                    <div><span class="font-semibold">Email</span><br>{{ $booking['email'] }}</div>
                    <div><span class="font-semibold">Phone Number</span><br>{{ $booking['phone'] }}</div>
                    <div><span class="font-semibold">Visit Date</span><br>{{ $booking['date'] }}</div>
                    <div><span class="font-semibold">Ticket Quantity</span><br>{{ $booking['quantity'] }} tickets</div>
                </div>
            </div>
            <!-- Order Summary -->
            <div class="flex flex-col p-8 w-full bg-white rounded-2xl shadow-xl md:w-96">
                <h2 class="mb-6 text-xl font-bold">Order Summary</h2>
                <div class="flex gap-4 items-center mb-4">
                    <img src="{{ $order['img'] ? (Str::startsWith($order['img'], ['http://', 'https://']) ? $order['img'] : asset('storage/' . $order['img'])) : 'https://via.placeholder.com/80x80?text=No+Image' }}" class="object-cover w-16 h-16 rounded-lg">
                    <div>
                        <div class="font-semibold">{{ $order['title'] }}</div>
                        <div class="text-sm text-gray-500">{{ $order['location'] }}</div>
                    </div>
                </div>
                <div class="flex justify-between mb-2 text-sm text-gray-700"><span>Price per ticket</span><span>Rp{{ number_format($order['price'],0,',','.') }}</span></div>
                <div class="flex justify-between mb-2 text-sm text-gray-700"><span>Quantity</span><span>&times; {{ $order['quantity'] }}</span></div>
                <div class="my-2 border-t"></div>
                <div class="flex justify-between items-center text-lg font-bold"><span>Total</span><span>Rp{{ number_format($order['total'],0,',','.') }}</span></div>
            </div>
        </div>
        <!-- Payment Method -->
        <div class="p-8 mb-10 w-full max-w-5xl bg-white rounded-2xl shadow-xl">
            <h2 class="mb-6 text-xl font-bold">Payment Method</h2>
            <form id="checkout-payment-form" action="/checkout/{{ $attraction->slug }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="name" value="{{ $booking['name'] }}">
                <input type="hidden" name="email" value="{{ $booking['email'] }}">
                <input type="hidden" name="phone" value="{{ $booking['phone'] }}">
                <input type="hidden" name="date" value="{{ $booking['date'] }}">
                <input type="hidden" name="quantity" value="{{ $booking['quantity'] }}">
                <div class="flex flex-col gap-4 mb-6 md:flex-row">
                    <label class="flex flex-col flex-1 justify-center items-center py-6 rounded-xl border-2 border-blue-400 transition cursor-pointer checkout-payment-option hover:shadow-lg" data-method="visa">
                        <input type="radio" name="payment" value="visa" class="hidden" required>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" class="mb-2 h-8">
                        <span class="font-semibold">Visa</span>
                    </label>
                    <label class="flex flex-col flex-1 justify-center items-center py-6 rounded-xl border-2 border-blue-400 transition cursor-pointer checkout-payment-option hover:shadow-lg" data-method="mastercard">
                        <input type="radio" name="payment" value="mastercard" class="hidden" required>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Mastercard-logo.png" alt="Mastercard" class="mb-2 h-8">
                        <span class="font-semibold">Mastercard</span>
                    </label>
                    <label class="flex flex-col flex-1 justify-center items-center py-6 rounded-xl border-2 border-blue-400 transition cursor-pointer checkout-payment-option hover:shadow-lg" data-method="paypal">
                        <input type="radio" name="payment" value="paypal" class="hidden" required>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" class="mb-2 h-8">
                        <span class="font-semibold">PayPal</span>
                    </label>
                </div>
                <div class="mb-6">
                    <label for="payment_proof" class="block mb-2 font-semibold">Upload Payment Proof <span class="text-red-500">*</span></label>
                    <input type="file" name="payment_proof" id="payment_proof" accept="image/*,application/pdf" required class="block p-2 w-full rounded-lg border border-gray-300">
                    @error('payment_proof')
                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="py-3 w-full text-lg font-bold text-white bg-blue-600 rounded-lg shadow transition hover:bg-blue-700">Pay Now</button>
            </form>
        </div>
    </div>
    <!-- Success Animation -->
    <div id="checkout-success-content" class="@if(!session('success')) hidden @endif flex flex-col items-center justify-center min-h-[60vh]">
        <div class="flex relative flex-col items-center">
            <div id="checkout-confetti" class="absolute inset-0 w-full h-full pointer-events-none"></div>
            <img src="https://cdn-icons-png.flaticon.com/512/2278/2278992.png" class="mb-6 w-32 h-32" alt="Success Icon">
            <h2 class="mb-2 text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-500 to-orange-400">Success Checkout</h2>
            <p class="mb-8 text-gray-400">Thank you for supporting us</p>
            <a href="/user/settings?tab=history" class="px-8 py-3 text-lg font-bold text-white bg-blue-600 rounded-full shadow transition hover:bg-blue-700">Check My Transactions</a>
        </div>
        @if(auth()->check())
        <div class="mt-12 w-full max-w-4xl">
            <h3 class="mb-6 text-2xl font-bold text-orange-500">You might also like</h3>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                @foreach(auth()->user()->recommendedAttractions() as $rec)
                    <div class="flex flex-col items-center p-4 bg-white rounded-xl shadow">
                        <img src="{{ $rec->img ? (Str::startsWith($rec->img, ['http://', 'https://']) ? $rec->img : asset('storage/' . $rec->img)) : 'https://via.placeholder.com/120x120?text=No+Image' }}" class="object-cover mb-3 w-28 h-28 rounded-lg">
                        <div class="mb-1 text-lg font-bold text-orange-600">{{ $rec->name }}</div>
                        <div class="mb-2 text-sm text-gray-500">{{ $rec->loc }}</div>
                        <div class="flex flex-wrap gap-1 mb-2">
                            @php $tags = is_array($rec->tags) ? $rec->tags : explode(',', $rec->tags); @endphp
                            @foreach($tags as $tag)
                                <span class="px-2 py-1 text-xs text-orange-600 bg-orange-100 rounded">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <a href="/destination/{{ $rec->slug }}" class="px-4 py-2 mt-auto text-sm font-semibold text-white bg-orange-500 rounded-lg shadow transition hover:bg-orange-600">View</a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
<style>
/* Only affect checkout page */
.checkout-payment-option.selected {
    border-color: #2563eb !important; /* blue-600 */
    box-shadow: 0 0 0 2px #93c5fd;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const options = document.querySelectorAll('.checkout-payment-option');
    options.forEach(opt => {
        opt.addEventListener('click', function() {
            options.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type=radio]').checked = true;
        });
    });
});
// Confetti animation after success (even after reload)
@if(session('success'))
window.onload = function() {
    const confetti = document.getElementById('checkout-confetti');
    if (confetti && confetti.childElementCount === 0) {
        for(let i=0;i<40;i++){
            const el = document.createElement('div');
            el.className = 'absolute';
            el.style.left = Math.random()*100+'%';
            el.style.top = Math.random()*60+'%';
            el.style.width = '12px';
            el.style.height = '12px';
            el.style.background = ['#fbbf24','#a78bfa','#f472b6','#f87171','#818cf8'][Math.floor(Math.random()*5)];
            el.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
            el.style.transform = `rotate(${Math.random()*360}deg)`;
            el.style.opacity = 0.7;
            confetti.appendChild(el);
        }
    }
}
@endif
</script>
@endsection 