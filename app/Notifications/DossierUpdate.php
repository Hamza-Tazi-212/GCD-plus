<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Notification;

class DossierUpdate extends Notification
{
    public function __construct(
        private string $title,
        private string $subject,
        private string $destination,
        private int $applicationId,
    ) {
    }

    public static function record(Application $application, string $title, string $destination): void
    {
        $application->candidate->notify(new self($title, $application->subject->title, $destination, $application->id));
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'subject' => $this->subject,
            'destination' => $this->destination,
            'application_id' => $this->applicationId,
        ];
    }
}
