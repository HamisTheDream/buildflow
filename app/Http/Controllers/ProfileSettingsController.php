<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $u = $request->user();

        return Inertia::render('App/Settings/Profile', [
            'user' => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'avatar_url' => $u->avatar_path ? asset('storage/' . $u->avatar_path) : null,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $u = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($u->id)],
            'current_password' => ['nullable', 'string'], // required only if changing email
            'avatar' => ['nullable', 'image', 'max:2048', new \App\Rules\SafeFile], // 2MB
        ]);

        $emailChanging = strtolower($data['email']) !== strtolower($u->email);

        if ($emailChanging) {
            if (!$request->filled('current_password')) {
                return back()->withErrors([
                    'current_password' => 'Current password is required to change email.',
                ]);
            }

            if (!Hash::check($request->string('current_password')->toString(), $u->password)) {
                return back()->withErrors([
                    'current_password' => 'Current password is incorrect.',
                ]);
            }
        }

        $u->name = $data['name'];
        $u->phone = $data['phone'] ?? null;
        $u->email = strtolower($data['email']);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $u->avatar_path = $path;
        }

        $u->save();

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $u = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $u->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $u->password = Hash::make($data['password']);
        $u->save();

        return back()->with('success', 'Password updated.');
    }
}
