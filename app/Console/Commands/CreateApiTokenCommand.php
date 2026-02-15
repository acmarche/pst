<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SfCommand;

final class CreateApiTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pst:create-api-token {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Sanctum API token for a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = $this->argument('username');

        $user = User::query()->where('username', $username)->first();

        if (! $user) {
            $this->error("User with username \"{$username}\" not found.");

            return SfCommand::FAILURE;
        }

        $token = $user->createToken('api-token');

        $this->info('API token created for '.$user->fullName().':');
        $this->newLine();
        $this->line($token->plainTextToken);

        return SfCommand::SUCCESS;
    }
}
