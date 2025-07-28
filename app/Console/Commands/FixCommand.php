<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SfCommand;

class FixCommand extends Command
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

        return SfCommand::SUCCESS;
    }

}
