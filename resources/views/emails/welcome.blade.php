<x-mail::message>
    # Welcome to BuildFlow, {{ explode(' ', $name)[0] }}!

    We're thrilled to have you on board. BuildFlow is designed to help you organize your construction projects, track daily site progress, and manage your budget seamlessly.

    **Here's what you can do right away:**
    - **Create your first project**
    - **Invite your team members**
    - **Start logging daily site activities**

    <x-mail::button :url="route('app.dashboard')">
        Go to Dashboard
    </x-mail::button>

    If you have any questions or need help exploring the platform, feel free to reply directly to this email or reach out to our support team.

    Let's build something amazing together,<br>
    The {{ config('app.name') }} Team
</x-mail::message>