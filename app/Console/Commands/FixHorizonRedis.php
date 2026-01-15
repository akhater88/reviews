<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class FixHorizonRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:fix-redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix corrupted Horizon Redis data by clearing master supervisor records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Attempting to fix Horizon Redis data...');

        try {
            // Get the raw Redis connection (without prefix)
            $redis = Redis::connection()->client();

            // Get the Laravel Redis prefix
            $laravelPrefix = config('database.redis.options.prefix', '');

            // Get the Horizon prefix
            $horizonPrefix = config('horizon.prefix', 'horizon:');

            $this->info("Laravel Redis prefix: {$laravelPrefix}");
            $this->info("Horizon prefix: {$horizonPrefix}");

            // Full prefix for Horizon keys
            $fullPrefix = $laravelPrefix . $horizonPrefix;

            $this->info("Looking for keys with pattern: {$fullPrefix}*");

            // Find all Horizon keys
            $keys = $redis->keys($fullPrefix . '*');

            if (empty($keys)) {
                $this->warn('No Horizon keys found. Trying alternative patterns...');

                // Try without Laravel prefix
                $keys = $redis->keys($horizonPrefix . '*');

                if (empty($keys)) {
                    // Try to find any keys with 'horizon' in them
                    $keys = $redis->keys('*horizon*');
                }
            }

            if (empty($keys)) {
                $this->info('No Horizon keys found in Redis.');
                $this->info('This might mean Redis is already clean or using a different configuration.');
                return Command::SUCCESS;
            }

            $this->info('Found ' . count($keys) . ' Horizon keys:');

            foreach ($keys as $key) {
                $this->line("  - {$key}");
            }

            if ($this->confirm('Do you want to delete all these keys?', true)) {
                foreach ($keys as $key) {
                    $redis->del($key);
                    $this->line("Deleted: {$key}");
                }

                $this->info('All Horizon keys have been deleted.');
                $this->info('You can now run: php artisan horizon');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());

            // Alternative approach using Laravel's Redis facade
            $this->info('Trying alternative approach...');

            try {
                // Use FLUSHDB to clear the current database
                if ($this->confirm('Do you want to flush the entire Redis database? (This will clear all data)', false)) {
                    Redis::connection()->flushdb();
                    $this->info('Redis database flushed successfully.');
                    $this->info('You can now run: php artisan horizon');
                }
            } catch (\Exception $e2) {
                $this->error('Alternative approach also failed: ' . $e2->getMessage());
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        }
    }
}
