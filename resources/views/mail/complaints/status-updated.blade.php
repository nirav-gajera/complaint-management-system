@php
$statusStyles = [
'pending' => 'background-color: #fef3c7; color: #92400e;',
'in_progress' => 'background-color: #e0f2fe; color: #075985;',
'resolved' => 'background-color: #d1fae5; color: #065f46;',
'rejected' => 'background-color: #ffe4e6; color: #9f1239;',
];

$statusStyle = $statusStyles[$complaint->status] ?? 'background-color: #f1f5f9; color: #334155;';
@endphp

<x-mail::message>
    # Complaint Status Updated

    Hello {{ $complaint->user->name ?? 'Customer' }},

    Your complaint **{{ $complaint->reference }}** has been updated. The current status is **{{ $statusLabel }}**.

    <x-mail::panel>
        **Reference:** {{ $complaint->reference }}

        **Title:** {{ $complaint->title }}

        **Category:** {{ $complaint->category?->name ?? 'Not specified' }}

        **Current Status:** <span style="display: inline-block; margin-top: 6px; padding: 6px 12px; border-radius: 9999px; font-size: 12px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; {{ $statusStyle }}">{{ $statusLabel }}</span>

        **Assigned To:** {{ $complaint->assignee?->name ?? 'Not assigned yet' }}

        **Submitted On:** {{ $complaint->submitted_at?->format('M d, Y h:i A') ?? 'Not available' }}

        @if ($complaint->resolved_at)
        **Resolved On:** {{ $complaint->resolved_at->format('M d, Y h:i A') }}
        @endif
    </x-mail::panel>

    ## Support Team Message

    {{ $messageText }}

    <x-mail::button :url="$complaintUrl">
        View Complaint Details
    </x-mail::button>

    If the button above does not work, copy and paste this link into your browser:

    {{ $complaintUrl }}

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>

