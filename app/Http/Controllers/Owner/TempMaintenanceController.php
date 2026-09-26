<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * TEMPORARY — one-off maintenance for the 2026-09-26 cleanup
 * (mail sender-name verification + stale test-user removal).
 * Remove this controller and its routes once the cleanup is done.
 */
class TempMaintenanceController extends Controller
{
    private const STALE_TEST_EMAILS = [
        'hamisahmed10+softdelete@gmail.com',
        'hamisahmed10+clickthrough@gmail.com',
    ];

    public function testMail()
    {
        Mail::raw('BuildFlow mail configuration test — please ignore.', function ($message) {
            $message->to('hamisahmed10+clickthrough@gmail.com')
                ->subject('BuildFlow mail config test');
        });

        return response()->json([
            'mail_from' => config('mail.from'),
            'mailer' => config('mail.default'),
            'sent_to' => 'hamisahmed10+clickthrough@gmail.com',
        ]);
    }

    public function deleteStaleTestUsers()
    {
        $deleted = [];
        $missing = [];

        DB::transaction(function () use (&$deleted, &$missing) {
            foreach (self::STALE_TEST_EMAILS as $email) {
                $user = User::where('email', $email)->first();
                if (! $user) {
                    $missing[] = $email;
                    continue;
                }
                // Dependent rows cascade / null out per migration FK rules.
                $user->delete();
                $deleted[] = $email;
            }
        });

        return response()->json(['deleted' => $deleted, 'missing' => $missing]);
    }
}
