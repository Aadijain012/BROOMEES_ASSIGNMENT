<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RecalculateReputation extends Command
{
    protected $signature = 'reputation:recalculate {--user= : Recalculate only the supplied user UUID}';

    protected $description = 'Recalculate stored reputation scores from authoritative relationship data';

    public function handle(ReputationService $reputation): int
    {
        $userId = $this->option('user');

        if (is_string($userId) && $userId !== '') {
            if (! Str::isUuid($userId)) {
                $this->error('The --user option must be a UUID.');

                return self::FAILURE;
            }

            $reputation->recalculateForUser(User::query()->findOrFail($userId));
            $this->info("Recalculated reputation for {$userId}.");

            return self::SUCCESS;
        }

        $reputation->recalculateAll();
        $this->info('Recalculated reputation for all users.');

        return self::SUCCESS;
    }
}
