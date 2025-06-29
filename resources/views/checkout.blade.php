@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center py-12">
    <div id="checkout-content" @if(session('success')) style="display:none" @endif>
        <div class="w-full max-w-5xl flex flex-col md:flex-row gap-8 mb-10">
            <!-- Booking Details -->
            <div class="flex-1 min-w-[260px] bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-xl font-bold mb-6">Booking</h2>
                <div class="space-y-2 text-gray-700">
                    <div><span class="font-semibold">Name</span><br>{{ $booking['name'] }}</div>
                    <div><span class="font-semibold">Email</span><br>{{ $booking['email'] }}</div>
                    <div><span class="font-semibold">Phone Number</span><br>{{ $booking['phone'] }}</div>
                    <div><span class="font-semibold">Visit Date</span><br>{{ $booking['date'] }}</div>
                    <div><span class="font-semibold">Ticket Quantity</span><br>{{ $booking['quantity'] }} tickets</div>
                </div>
            </div>
            <!-- Order Summary -->
            <div class="w-full md:w-96 bg-white rounded-2xl shadow-xl p-8 flex flex-col">
                <h2 class="text-xl font-bold mb-6">Order Summary</h2>
                <div class="flex items-center gap-4 mb-4">
                    <img src="{{ $order['img'] }}" class="w-16 h-16 rounded-lg object-cover">
                    <div>
                        <div class="font-semibold">{{ $order['title'] }}</div>
                        <div class="text-gray-500 text-sm">{{ $order['location'] }}</div>
                    </div>
                </div>
                <div class="text-gray-700 text-sm mb-2 flex justify-between"><span>Price per ticket</span><span>Rp{{ number_format($order['price'],0,',','.') }}</span></div>
                <div class="text-gray-700 text-sm mb-2 flex justify-between"><span>Quantity</span><span>&times; {{ $order['quantity'] }}</span></div>
                <div class="border-t my-2"></div>
                <div class="flex justify-between items-center font-bold text-lg"><span>Total</span><span>Rp{{ number_format($order['total'],0,',','.') }}</span></div>
            </div>
        </div>
        <!-- Payment Method -->
        <div class="w-full max-w-5xl bg-white rounded-2xl shadow-xl p-8 mb-10">
            <h2 class="text-xl font-bold mb-6">Payment Method</h2>
            <form id="checkout-payment-form" action="/checkout/{{ $attraction->slug }}" method="POST">
                @csrf
                <input type="hidden" name="name" value="{{ $booking['name'] }}">
                <input type="hidden" name="email" value="{{ $booking['email'] }}">
                <input type="hidden" name="phone" value="{{ $booking['phone'] }}">
                <input type="hidden" name="date" value="{{ $booking['date'] }}">
                <input type="hidden" name="quantity" value="{{ $booking['quantity'] }}">
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <label class="checkout-payment-option flex-1 border-2 border-blue-400 rounded-xl flex flex-col items-center justify-center py-6 cursor-pointer hover:shadow-lg transition" data-method="visa">
                        <input type="radio" name="payment" value="visa" class="hidden" required>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" class="h-8 mb-2">
                        <span class="font-semibold">Visa</span>
                    </label>
                    <label class="checkout-payment-option flex-1 border-2 border-blue-400 rounded-xl flex flex-col items-center justify-center py-6 cursor-pointer hover:shadow-lg transition" data-method="mastercard">
                        <input type="radio" name="payment" value="mastercard" class="hidden" required>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Mastercard-logo.png" alt="Mastercard" class="h-8 mb-2">
                        <span class="font-semibold">Mastercard</span>
                    </label>
                    <label class="checkout-payment-option flex-1 border-2 border-blue-400 rounded-xl flex flex-col items-center justify-center py-6 cursor-pointer hover:shadow-lg transition" data-method="paypal">
                        <input type="radio" name="payment" value="paypal" class="hidden" required>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" class="h-8 mb-2">
                        <span class="font-semibold">PayPal</span>
                    </label>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-bold text-lg shadow transition">Pay Now</button>
            </form>
        </div>
    </div>
    <!-- Success Animation -->
    <div id="checkout-success-content" class="@if(!session('success')) hidden @endif flex flex-col items-center justify-center min-h-[60vh]">
        <div class="relative flex flex-col items-center">
            <div id="checkout-confetti" class="absolute inset-0 w-full h-full pointer-events-none"></div>
            <img src="https://cdn-icons-png.flaticon.com/512/2278/2278992.png" class="w-32 h-32 mb-6" alt="Success Icon">
            <h2 class="text-4xl font-extrabold mb-2 bg-gradient-to-r from-purple-500 to-orange-400 bg-clip-text text-transparent">Success Checkout</h2>
            <p class="text-gray-400 mb-8">Thank you for supporting us</p>
            <a href="/user/settings?tab=history" class="px-8 py-3 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg shadow transition">Check My Transactions</a>
        </div>
        @if(auth()->check())
        <div class="mt-12 w-full max-w-4xl">
            <h3 class="text-2xl font-bold mb-6 text-orange-500">You might also like</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach(auth()->user()->recommendedAttractions() as $rec)
                    <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
                        <img src="{{ $rec->img ?? 'https://via.placeholder.com/120x120?text=No+Image' }}" class="w-28 h-28 object-cover rounded-lg mb-3">
                        <div class="font-bold text-lg text-orange-600 mb-1">{{ $rec->name }}</div>
                        <div class="text-gray-500 text-sm mb-2">{{ $rec->loc }}</div>
                        <div class="flex flex-wrap gap-1 mb-2">
                            @php $tags = is_array($rec->tags) ? $rec->tags : explode(',', $rec->tags); @endphp
                            @foreach($tags as $tag)
                                <span class="bg-orange-100 text-orange-600 px-2 py-1 rounded text-xs">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <a href="/destination/{{ $rec->slug }}" class="mt-auto px-4 py-2 bg-orange-500 text-white rounded-lg font-semibold text-sm shadow hover:bg-orange-600 transition">View</a>
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