<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;

class PrivacyController extends Controller
{
    /**
     * Display the Privacy settings page.
     */
    public function edit(Request $request)
    {
        return Inertia::render('Profile/Privacy');
    }

    /**
     * GDPR Right to Data Portability: Export user data.
     */
    public function exportData(Request $request)
    {
        $user = $request->user();

        // Gather all relevant data associated with the user
        $data = [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->toIso8601String(),
                'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toIso8601String() : null,
            ],
            'organizations' => $user->organizations()->select('organizations.id', 'organizations.name', 'organizations.created_at')->get(),
            'analytics_visits' => \App\Models\PageVisit::where('user_id', $user->id)
                ->select('url', 'created_at', 'ip_address', 'device_type', 'browser', 'country')
                ->get(),
        ];

        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
        $fileName = 'buildflow_data_export_' . now()->format('Y-m-d_H-i-s') . '.json';

        return Response::make($jsonContent, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * GDPR Right to Erasure: Delete user account and associated PII data.
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Nullify related analytics records to preserve overall stats but remove PII link
        \App\Models\PageVisit::where('user_id', $user->id)->update(['user_id' => null]);

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
