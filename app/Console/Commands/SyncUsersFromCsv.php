<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

final class SyncUsersFromCsv extends Command
{
    protected $signature = 'app:sync-users-from-csv
                            {--file=data/Listeagents.csv : Path to the CSV file}
                            {--force : Actually delete users (dry-run by default)}';

    protected $description = 'Delete users not present in the CSV file';

    public function handle(): int
    {
        $filePath = base_path($this->option('file'));

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $csvEmails = $this->extractEmailsFromCsv($filePath);

        if ($csvEmails->isEmpty()) {
            $this->error('No emails found in CSV file.');

            return self::FAILURE;
        }

        $this->info("Found {$csvEmails->count()} users in CSV file.");

        $usersToDelete = User::query()
            ->whereNotIn('email', $csvEmails)
            ->get();

        if ($usersToDelete->isEmpty()) {
            $this->info('No users to delete. All database users are in the CSV.');

            return self::SUCCESS;
        }

        $this->warn("Users to delete ({$usersToDelete->count()}):");
        $this->table(
            ['ID', 'Name', 'Email'],
            $usersToDelete->map(fn (User $user) => [
                $user->id,
                $user->fullName(),
                $user->email,
            ])
        );

        if (! $this->option('force')) {
            $this->newLine();
            $this->info('Dry-run mode. Run with --force to actually delete these users.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Are you sure you want to delete these users?')) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $deletedCount = User::query()
            ->whereNotIn('email', $csvEmails)
            ->delete();

        $this->info("Deleted {$deletedCount} users.");

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function extractEmailsFromCsv(string $filePath): Collection
    {
        $emails = collect();

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return $emails;
        }

        $isFirstLine = true;
        while (($line = fgets($handle)) !== false) {
            if ($isFirstLine) {
                $isFirstLine = false;

                continue;
            }

            $line = mb_trim($line);
            if ($line === '') {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) >= 3) {
                $email = mb_trim($parts[2]);
                if ($email !== '') {
                    $emails->push($email);
                }
            }
        }

        fclose($handle);

        return $emails;
    }
}
