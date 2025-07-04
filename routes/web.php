<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Models\Attraction;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    $attractions = \App\Models\Attraction::latest()->take(8)->get();
    $recommendations = collect();
    $featured = \App\Models\Attraction::inRandomOrder()->first();
    if (auth()->check() && auth()->user()->bookings()->exists()) {
        $recommendations = auth()->user()->recommendedAttractions(4);
    }
    return view('landing', [
        'attractions' => $attractions,
        'recommendations' => $recommendations,
        'featured' => $featured,
    ]);
})->name('landing');

Route::get('/destination', function (Request $request) {
    $query = $request->input('q');
    $attractions = Attraction::query();
    $suggestions = collect();
    if ($query) {
        // First, try to find matches by name or tags (case-insensitive)
        $attractions = $attractions->where(function($q2) use ($query) {
            $q2->where('name', 'like', "%$query%")
                ->orWhereJsonContains('tags', $query)
                ->orWhereRaw('LOWER(tags) LIKE ?', ["%" . strtolower($query) . "%"]); // fallback for string tags
        });
        $results = $attractions->paginate(8)->withQueryString();
        if ($results->total() === 0) {
            // Fuzzy search fallback: get all, then filter by similar_text
            $all = Attraction::all();
            $scored = $all->map(function($item) use ($query) {
                $max = 0;
                similar_text(strtolower($item->name), strtolower($query), $p1);
                $max = max($max, $p1);
                $tags = is_array($item->tags) ? $item->tags : explode(',', $item->tags);
                foreach ($tags as $tag) {
                    similar_text(strtolower($tag), strtolower($query), $p2);
                    $max = max($max, $p2);
                }
                return ['item' => $item, 'score' => $max];
            })->sortByDesc('score')->filter(function($x) { return $x['score'] > 30; })->take(8);
            $suggestions = $scored->pluck('item');
        }
        $attractions = $results;
    } else {
        $attractions = $attractions->paginate(8)->withQueryString();
    }
    return view('destination', ['attractions' => $attractions, 'query' => $query, 'suggestions' => $suggestions]);
})->name('destination');

Route::get('/destination/{slug}', function ($slug) {
    $attraction = Attraction::where('slug', $slug)->firstOrFail();
    // Recommend based on tag similarity
    $recommendedAttractions = \App\Models\Attraction::where('id', '!=', $attraction->id)
        ->get()
        ->sortByDesc(function($item) use ($attraction) {
            $tags1 = collect($attraction->tags ?? []);
            $tags2 = collect($item->tags ?? []);
            $intersection = $tags1->intersect($tags2)->count();
            $union = $tags1->merge($tags2)->unique()->count();
            return $union > 0 ? $intersection / $union : 0;
        })->take(4);
    return view('destinationn', [
        'attraction' => $attraction,
        'recommendedAttractions' => $recommendedAttractions
    ]);
})->name('destination.detail');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user/settings', function (Request $request) {
        $tab = $request->input('tab', 'settings');
        $bookings = auth()->user()->bookings()->with('attraction')->latest()->get();
        return view('user.settings', ['tab' => $tab, 'bookings' => $bookings]);
    })->name('user.settings');

    Route::get('/checkout/{slug}', function (Request $request, $slug) {
        $attraction = \App\Models\Attraction::where('slug', $slug)->firstOrFail();
        $booking = [
            'name' => $request->input('name', auth()->user()->name),
            'email' => $request->input('email', auth()->user()->email),
            'phone' => $request->input('phone'),
            'date' => $request->input('date'),
            'quantity' => (int)($request->input('quantity', 1)),
        ];
        // Redirect if any booking data is missing
        if (!$booking['phone'] || !$booking['date'] || !$booking['quantity']) {
            return redirect()->route('destination.detail', ['slug' => $slug])
                ->with('error', 'Please fill out the booking form first.');
        }
        $price = (int) preg_replace('/[^0-9]/', '', $attraction->price ?? '0');
        $quantity = $booking['quantity'] > 0 ? $booking['quantity'] : 1;
        $order = [
            'title' => $attraction->name,
            'location' => $attraction->loc ?? '',
            'img' => $attraction->img ?? '',
            'price' => $price,
            'quantity' => $quantity,
            'total' => $price * $quantity,
        ];
        return view('checkout', compact('booking', 'order', 'attraction'));
    })->name('checkout');

    Route::post('/checkout/{slug}', function (Request $request, $slug) {
        $attraction = \App\Models\Attraction::where('slug', $slug)->firstOrFail();
        $price = (int) preg_replace('/[^0-9]/', '', $attraction->price ?? '0');
        $quantity = (int) $request->input('quantity', 1);
        $total = $price * ($quantity > 0 ? $quantity : 1);
        $validated = $request->validate([
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');
        }
        $booking = \App\Models\Booking::create([
            'user_id' => auth()->id(),
            'attraction_id' => $attraction->id,
            'name' => $request->input('name', auth()->user()->name),
            'email' => $request->input('email', auth()->user()->email),
            'phone' => $request->input('phone'),
            'date' => $request->input('date'),
            'quantity' => $quantity,
            'total' => $total,
            'payment_method' => $request->input('payment', 'visa'),
            'status' => 'pending',
            'payment_proof' => $paymentProofPath,
        ]);
        // Redirect back to checkout with all booking data and success message
        return redirect()->route('checkout', [
            'slug' => $slug,
            'name' => $request->input('name', auth()->user()->name),
            'email' => $request->input('email', auth()->user()->email),
            'phone' => $request->input('phone'),
            'date' => $request->input('date'),
            'quantity' => $quantity,
        ])->with('success', 'Booking successful!');
    });
});

// Export attractions as JSON
Route::get('/export-attractions', function () {
    \File::put(base_path('attractions.json'), \App\Models\Attraction::all()->toJson(JSON_PRETTY_PRINT));
    return 'attractions.json exported!';
});

// Export bookings as JSON
Route::get('/export-bookings', function () {
    \File::put(base_path('bookings.json'), \App\Models\Booking::with(['user', 'attraction'])->get()->toJson(JSON_PRETTY_PRINT));
    return 'bookings.json exported with status information!';
});

require __DIR__.'/auth.php';
