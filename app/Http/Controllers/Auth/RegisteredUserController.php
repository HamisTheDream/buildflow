<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Plan;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'account_type' => ['required', Rule::in(['individual', 'company'])],
            'organization_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Honeypot check
        if ($request->filled('website')) {
            // Silently fail or redirect
            return redirect()->route('login');
        }

        $accountType = $request->string('account_type')->toString();

        if ($accountType === 'company' && !$request->filled('organization_name')) {
            return back()->withErrors([
                'organization_name' => 'Organization name is required for company accounts.',
            ]);
        }

        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $accountType) {
            $user = User::create([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->lower()->toString(),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->string('password')->toString()),
                'is_invited_only' => false,
            ]);

            // Create org based on account type
            $orgName = $accountType === 'company'
                ? $request->string('organization_name')->toString()
                : $user->name . "'s Workspace";

            $org = Organization::create([
                'name' => $orgName,
                'type' => $accountType,
                'currency' => $request->input('currency', 'NGN'),
                // New organizations start on the Free plan so plan-based
                // filters and limits see truthful data (previously plan_id
                // was left NULL and the UI papered over it with a "Free"
                // fallback, which broke the owner plan filter).
                'plan_id' => Plan::where('key', 'free')->value('id'),
            ]);

            // Attach user as owner
            $org->users()->syncWithoutDetaching([
                $user->id => ['role' => 'owner'],
            ]);

            $user->current_organization_id = $org->id;
            $user->save();

            return $user;
        });

        // The Registered event triggers the verification email. Mail delivery is
        // best-effort here: if SMTP is down we must not break signup — the user
        // lands on the verification notice page and can resend from there.
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            report($e);
        }
        Auth::login($user);

        return redirect()->route('app.dashboard');
    }
}
