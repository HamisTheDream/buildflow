<x-mail::message>
    # Welcome onto the Board, {{ $name }}!

    We appear to be excited to have you on board. BuildFlow is designed to help you streamline your construction projects and manage your team effectively.

    <x-mail::button :url="$url">
        Get Started
    </x-mail::button>

    Here are a few things you can do to get started:
    - Setup your organization profile
    - Invite your team members
    - Create your first project

    If you have any questions, feel free to reply to this email or visit our [Help Center]({{ route('app.support.index') }}).

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>