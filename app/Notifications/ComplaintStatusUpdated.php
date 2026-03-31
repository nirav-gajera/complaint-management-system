<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public Complaint $complaint,
        public string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Complaint {$this->complaint->reference} status updated")
            ->markdown('mail.complaints.status-updated', [
                'complaint' => $this->complaint,
                'messageText' => $this->message,
                'complaintUrl' => url("/complaints/{$this->complaint->id}"),
                'statusLabel' => $this->formatStatus($this->complaint->status),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'reference' => $this->complaint->reference,
            'status' => $this->complaint->status,
            'message' => $this->message,
        ];
    }

    protected function formatStatus(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }
}
