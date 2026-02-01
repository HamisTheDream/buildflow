@component('mail::message')
# You’re invited to join {{ $orgName }}

You’ve been invited to join **{{ $orgName }}** on BuildFlow as **{{ $role }}**.

@component('mail::button', ['url' => $inviteUrl])
Accept Invite
@endcomponent

@if($expiresAt)
This invite expires on: **{{ $expiresAt }}**
@endif

If you didn’t expect this invite, you can ignore this email.

Thanks,  
{{ config('app.name') }}
@endcomponent
