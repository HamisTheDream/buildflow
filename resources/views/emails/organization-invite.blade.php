<x-mail::message>
    # Join {{ $orgName }} on BuildFlow

    Hello,

    You have been invited to join the team at **{{ $orgName }}** on BuildFlow. You have been assigned the role of **{{ $role }}**.

    <x-mail::button :url="$inviteUrl">
        Join Team
    </x-mail::button>

    @if($expiresAt)
    This invitation will expire on **{{ $expiresAt }}**.
    @endif

    If you were not expecting this invitation, you can safely ignore this email.

    Welcome aboard,<br>
    The {{ config('app.name') }} Team
</x-mail::message>