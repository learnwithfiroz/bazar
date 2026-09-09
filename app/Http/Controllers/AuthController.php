<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Ultra-Fast Login Screen with zero memory overhead
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        // Remember login credentials
        $remember = $request->boolean('remember', true);

        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            
            // Record login timestamp and IP address for security tracking
            Auth::user()->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'প্রদত্ত মোবাইল/ইমেইল অথবা পাসওয়ার্ডটি সঠিক নয়।',
        ])->onlyInput('login');
    }

    public function quickLogin(User $user)
    {
        Auth::login($user, true);
        request()->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        return redirect()->route('dashboard')->with('success', "স্বাগতম {$user->name}! ({$user->role_display_name})");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
