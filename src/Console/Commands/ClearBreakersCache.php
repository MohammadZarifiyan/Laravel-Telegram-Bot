<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\CacheManager;

class ClearBreakersCache extends Command
{
    protected $signature = 'telegram:clear-breakers-cache';

    protected $description = 'Remove the Telegram breakers cache file';

    public function handle(): void
    {
        /**
         * @var CacheManager $cache_manager
         */
        $cache_manager = App::make(CacheManager::class);

        if (!$cache_manager->exists('breakers.json')) {
            $this->info('There is no Telegram breakers cache to delete.');

            return;
        }

        if ($cache_manager->delete('breakers.json')) {
            $this->info('Telegram breakers cache cleared successfully.');
        }
        else {
            $this->info('Failed to remove Telegram breakers cache!');
        }
    }
}
