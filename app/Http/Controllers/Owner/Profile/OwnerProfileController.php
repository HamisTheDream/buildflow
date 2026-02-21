<?php

namespace App\Http\Controllers\Owner\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class OwnerProfileController extends Controller
{
    public function edit()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('owner')->user();

        return Inertia::render('Owner/Profile/Edit', [
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'avatar_path' => $admin->avatar_path,
                'avatar_url' => $admin->avatar_path ? asset('storage/' . $admin->avatar_path) : null,
                'is_super' => $admin->is_super,
                'last_login_at' => $admin->last_login_at?->toDateTimeString(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('owner')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,' . $admin->id],
        ]);

        $admin->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateAvatar(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('owner')->user();

        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', new \App\Rules\SafeFile], // 2MB max
        ]);

        // Delete old avatar if exists
        if ($admin->avatar_path) {
            Storage::delete($admin->avatar_path);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars/admins');

        $admin->update(['avatar_path' => $path]);

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function deleteAvatar()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('owner')->user();

        if ($admin->avatar_path) {
            Storage::delete($admin->avatar_path);
            $admin->update(['avatar_path' => null]);
        }

        return back()->with('success', 'Profile picture removed successfully.');
    }

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('owner')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:owner'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
