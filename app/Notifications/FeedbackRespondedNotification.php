<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FeedbackRespondedNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'feedback_responded',
            'pr_id'       => $this->data['pr_id'],
            'pr_number'   => $this->data['pr_number'],
            'department'  => $this->data['department'],
            'response'    => $this->data['response'],
            'message'     => "Dept {$this->data['department']} merespon feedback untuk PR {$this->data['pr_number']}: \"{$this->data['response_preview']}\"",
        ];
    }
}
