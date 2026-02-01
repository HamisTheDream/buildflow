<?php

namespace App\Http\Controllers\Owner\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OwnerAuthController extends Controller
{
    public function showLogin()
    {
        return \Inertia\Inertia::render('Owner/Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (!Auth::guard('owner')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid login credentials.',
            ]);
        }

        $request->session()->regenerate();

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('owner')->user();
        $admin->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended('/owner/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('owner')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/owner/login');
    }
}
