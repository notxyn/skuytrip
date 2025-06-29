<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
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
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $redirectTo = session('url.intended', route('landing'));
        return redirect($redirectTo)->with('status', "You're logged in!");
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
