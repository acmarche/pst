<?php

namespace App\Observers;

use App\Enums\DepartmentEnum;
use App\Models\Action;
use App\Models\TracksHistoryTrait;

class ActionObserver
{
    use TracksHistoryTrait;

    /**
     * Handle the Action "created" event.
     */
    public function created(Action $action): void
    {
        if ($action->department == DepartmentEnum::VILLE->value) {
            $email = config('pst')['validator']['email'];
            try {
            /*    Mail::to(new Address($email, 'As'))
                    ->send(new ActionNewMail($action));*/
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
    }

    /**
     * Handle the Action "updated" event.
     */
    public function updated(Action $action): void
    {
        $this->track($action);
    }

    /**
     * Handle the Action "deleted" event.
     */
    public function deleted(Action $action): void
    {
        // ...
    }

    /**
     * Handle the Action "restored" event.
     */
    public function restored(Action $action): void
    {
        // ...
    }

    /**
     * Handle the Action "forceDeleted" event.
     */
    public function forceDeleted(Action $action): void
    {
        // ...
    }

}
