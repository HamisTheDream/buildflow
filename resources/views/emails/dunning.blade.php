<x-mail::message>
  # Action Required: Subscription Notice

  Hello {{ $org->name }},

  @if($stage === 1)
  Your subscription has entered a grace period. Please renew to avoid interruption to your services.
  @elseif($stage === 2)
  This is a reminder to renew your BuildFlow subscription.
  @else
  **Final Notice:** Your access may be paused if payment is not completed immediately.
  @endif

  @if($graceEndsAt)
  **Grace Period Ends:** {{ $graceEndsAt->toDayDateTimeString() }}
  @endif

  <x-mail::button :url="$billingUrl">
    Renew Subscription
  </x-mail::button>

  If you have already made payment, please ignore this message.

  Thanks,<br>
  {{ config('app.name') }}
</x-mail::message>