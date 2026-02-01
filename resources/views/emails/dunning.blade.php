<!doctype html>
<html>
  <body style="font-family: Arial, sans-serif;">
    <h2>BuildFlow subscription notice</h2>

    <p>Hello {{ $org->name }},</p>

    @if($stage === 1)
      <p>Your subscription has entered a grace period. Please renew to avoid interruption.</p>
    @elseif($stage === 2)
      <p>This is a reminder to renew your BuildFlow subscription.</p>
    @else
      <p><strong>Final notice:</strong> your access may be paused if payment is not completed.</p>
    @endif

    @if($graceEndsAt)
      <p>Grace period ends: <strong>{{ $graceEndsAt->toDayDateTimeString() }}</strong></p>
    @endif

    <p>
      <a href="{{ $billingUrl }}">Open Billing</a>
    </p>

    <p>— BuildFlow Team</p>
  </body>
</html>
