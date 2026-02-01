<?php

namespace App\Http\Controllers\Owner\Billing;

use App\Http\Controllers\Controller;
use App\Models\PaystackWebhookEvent;
use Inertia\Inertia;
use Illuminate\Http\Request;

class OwnerWebhooksController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $event = (string) $request->query('event', '');

        $rows = PaystackWebhookEvent::query()
            ->when($q, fn($query) => $query->where('reference', 'like', "%{$q}%"))
            ->when($event, fn($query) => $query->where('event', $event))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn($e) => [
                'id' => $e->id,
                'event' => $e->event,
                'reference' => $e->reference,
                'received_at' => $e->received_at?->toDateTimeString(),
            ]);

        return Inertia::render('Owner/Billing/Webhooks/Index', [
            'filters' => [
                'q' => $q,
                'event' => $event,
            ],
            'events' => $rows,
        ]);
    }

    public function show(PaystackWebhookEvent $event)
    {
        return Inertia::render('Owner/Billing/Webhooks/Show', [
            'event' => [
                'id' => $event->id,
                'event' => $event->event,
                'reference' => $event->reference,
                'received_at' => $event->received_at?->toDateTimeString(),
                'payload' => $event->payload,
            ],
        ]);
    }
}
