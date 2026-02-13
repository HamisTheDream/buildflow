<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Owner/Settings/Index', [
            'plans' => Plan::orderBy('price_monthly_cents')->get(),
            'settings' => Setting::all()->pluck('value', 'key'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'plans' => 'array',
            'plans.*.id' => 'required|exists:plans,id',
            'plans.*.price_monthly_cents' => 'required|integer|min:0',
            'settings' => 'array',
            'settings.support_email' => 'nullable|email',
            'settings.seo_logo' => 'nullable|file|image|max:2048',
            'settings.site_icon' => 'nullable|file|image|max:1024', // New Icon (Favicon/App Icon)
            'settings.site_title' => 'nullable|string|max:255',
            'settings.site_description' => 'nullable|string|max:500',
            'settings.site_keywords' => 'nullable|string|max:500',
        ]);

        // Update Plans
        if ($request->has('plans')) {
            foreach ($request->plans as $planData) {
                Plan::where('id', $planData['id'])->update([
                    'price_monthly_cents' => $planData['price_monthly_cents']
                ]);
            }
        }

        // Update Settings
        if ($request->has('settings')) {
            foreach ($request->settings as $key => $value) {
                // Handle File Uploads (SEO Logo & Site Icon)
                if (in_array($key, ['seo_logo', 'site_icon'])) {
                    if ($request->hasFile("settings.$key")) {
                        $path = $request->file("settings.$key")->store('settings', 'public');
                        $value = $path;
                    } elseif (!is_string($value)) {
                        // If it's not a new file and not a string (existing path), skip updates
                        continue;
                    }
                }

                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
