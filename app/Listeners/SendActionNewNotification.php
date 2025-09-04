<?php

namespace App\Listeners;

use App\Events\ActionProcessed;
use App\Mail\ActionNewMail;
use App\Models\Action;
use Exception;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Address;

final class SendActionNewNotification
{
    public function handle(ActionProcessed $event): void
    {
        $action = $event->action();
        $this->sendMail($action);
    }

    private function sendMail(Action $action): void
    {
        try {
            Mail::to(new Address(config('pst.validator.email')))
                ->send(new ActionNewMail($action));
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
