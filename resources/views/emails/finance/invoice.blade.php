<x-mail::message>
    # New Invoice from {{ $invoice->organization->name }}

    Hi,

    Please find attached your invoice **{{ $invoice->number }}** from **{{ $invoice->organization->name }}**.

    **Invoice Amount:** {{ $invoice->currency }} {{ number_format($invoice->total_amount_cents / 100, 2) }}<br>
    **Due Date:** {{ $invoice->due_date->format('M d, Y') }}

    <x-mail::button :url="$url">
        View & Pay Invoice
    </x-mail::button>

    If you have any questions, please contact us.

    Best regards,<br>
    {{ $invoice->organization->name }}
</x-mail::message>