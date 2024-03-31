<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Interfaces\CacheManager;
use MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider;
use ReflectionClass;

class OptimizeBreakers extends Command
{
    protected $signature = 'telegram:cache-breakers';

    protected $description = 'Cache the Telegram bot breakers';

    public function handle(): void
    {
        $breakers = (array) config('telegram.breakers');
        $cache = [];

        foreach ($breakers as $breaker) {
            $reflection_class = new ReflectionClass($breaker);

            foreach ($reflection_class->getMethods() as $method) {
                if ($method->getName() === 'handle') {
                    $cache['all'][] = $breaker;
                }
                else if (str_starts_with('handle', $method->getName())) {
                    $update_type = str_replace('handle', '', $method->getName());
                    $update_type = Str::snake($update_type);

                    if (!in_array($update_type, TelegramServiceProvider::UPDATE_TYPES)) {
                        continue;
                    }

                    if (array_key_exists($update_type, $cache)) {
                        $cache[$update_type][] = $breaker;
                    }
                    else {
                        $cache[$update_type] = [$breaker];
                    }
                }
            }
        }

        /**
         * @var CacheManager $cache_manager
         */
        $cache_manager = App::make(CacheManager::class);

        if ($cache_manager->put('breakers.json', json_encode($cache))) {
            $this->info('Telegram breakers cached successfully.');
        }
        else {
            $this->error('Failed to cache Telegram breakers!');
        }
    }
}
