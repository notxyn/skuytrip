<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $previous = url()->previous();
        $loginUrls = [route('login', [], false), route('register', [], false), route('password.request', [], false), route('password.reset', ['token' => 'dummy'], false)];
        $isAuthPage = false;
        foreach ($loginUrls as $url) {
            if (str_contains($previous, $url)) {
                $isAuthPage = true;
                break;
            }
        }
        if (!$isAuthPage && $previous !== url()->current()) {
            session(['url.intended' => $previous]);
        }
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $redirectTo = session('url.intended', route('landing'));
        return redirect($redirectTo)->with('status', 'You\'re registered and logged in!');
    }
}
