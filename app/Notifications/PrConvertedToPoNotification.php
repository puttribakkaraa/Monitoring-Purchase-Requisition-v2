<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrConvertedToPoNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $prData;

    /**
     * Create a new notification instance.
     */
    public function __construct($prData)
    {
        $this->prData = $prData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pr_number' => $this->prData['pr_number'],
            'po_number' => $this->prData['po_number'],
            'po_date' => $this->prData['po_date'],
            'message' => "PR {$this->prData['pr_number']} has been converted to PO {$this->prData['po_number']} on {$this->prData['po_date']}",
        ];
    }
}
