<?php

namespace App\Notifications;

use App\Models\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class PdfExportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Action $action,
        public string $downloadUrl,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'action_id' => $this->action->id,
            'action_name' => $this->action->name,
            'download_url' => $this->downloadUrl,
        ];
    }
}
