<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\CacheManager;

class ClearMiddlewaresCache extends Command
{
    protected $signature = 'telegram:clear-middlewares-cache';

    protected $description = 'Remove the Telegram middlewares cache file';

    public function handle(): void
    {
        /**
         * @var CacheManager $cache_manager
         */
        $cache_manager = App::make(CacheManager::class);

        if (!$cache_manager->exists('middlewares.json')) {
            $this->info('There is no Telegram middlewares cache to delete.');

            return;
        }

        if ($cache_manager->delete('middlewares.json')) {
            $this->info('Telegram middlewares cache cleared successfully.');
        }
        else {
            $this->info('Failed to remove Telegram middlewares cache!');
        }
    }
}
