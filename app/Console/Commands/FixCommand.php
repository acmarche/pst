<?php

namespace App\Console\Commands;

use App\Models\Action;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SfCommand;

final class FixCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pst:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test command';

    public function handle(): int
    {
        $actions = Action::all();
        foreach ($actions as $action) {
            if ($action->isInternal()) {
                $action->update(['is_internal' => true]);
            }
        }

        return SfCommand::SUCCESS;
    }
}
