<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Auto-accept pending invite token after login
        $pendingToken = $request->session()->pull('pending_invite_token');

        if ($pendingToken) {
            $invite = \App\Models\OrganizationInvite::query()
                ->where('token', $pendingToken)
                ->with('organization')
                ->first();

            if ($invite && !$invite->accepted_at) {
                $emailMatches = strtolower($invite->email) === strtolower($request->user()->email);

                $notExpired = !$invite->expires_at || now()->lessThanOrEqualTo($invite->expires_at);

                if ($emailMatches && $notExpired) {
                    // attach membership + mark accepted + set current org
                    $org = $invite->organization;

                    $org->users()->syncWithoutDetaching([
                        $request->user()->id => ['role' => $invite->role],
                    ]);

                    $invite->accepted_at = now();
                    $invite->accepted_by = $request->user()->id;
                    $invite->save();

                    $request->user()->current_organization_id = $org->id;
                    $request->user()->save();

                    return redirect()->route('app.dashboard')
                        ->with('success', "Invite accepted. Welcome to {$org->name}.");
                }
            }
        }


        return redirect()->intended(route('dashboard', absolute: false));
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
