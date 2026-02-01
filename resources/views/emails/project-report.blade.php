<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <p><strong>{{ $title }}</strong></p>
    <p>Project: {{ $projectName }}</p>

    @if($customMessage)
        <p>{!! nl2br(e($customMessage)) !!}</p>
    @endif

    <p>Attached is your BuildFlow report PDF.</p>
</body>
</html>
