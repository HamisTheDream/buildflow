<x-mail::message>
    # {{ $title }}

    **Project:** {{ $projectName }}

    @if($customMessage)
    {{ $customMessage }}
    @endif

    Attached is your BuildFlow report PDF.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>