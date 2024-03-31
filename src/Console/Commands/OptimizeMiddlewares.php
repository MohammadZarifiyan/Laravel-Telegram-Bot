<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Interfaces\CacheManager;
use MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider;
use ReflectionClass;

class OptimizeMiddlewares extends Command
{
    protected $signature = 'telegram:cache-middlewares';

    protected $description = 'Cache the Telegram bot middlewares';

    public function handle(): void
    {
        $middlewares = (array) config('telegram.middlewares');
        $cache = [];

        foreach ($middlewares as $middleware) {
            $reflection_class = new ReflectionClass($middleware);

            foreach ($reflection_class->getMethods() as $method) {
                if ($method->getName() === 'handle') {
                    $cache['all'][] = $middleware;
                }
                else if (str_starts_with($method->getName(), 'handle')) {
                    $update_type = str_replace('handle', '', $method->getName());
                    $update_type = Str::snake($update_type);

                    if (!in_array($update_type, TelegramServiceProvider::UPDATE_TYPES)) {
                        continue;
                    }

                    if (array_key_exists($update_type, $cache)) {
                        $cache[$update_type][] = $middleware;
                    }
                    else {
                        $cache[$update_type] = [$middleware];
                    }
                }
            }
        }

        /**
         * @var CacheManager $cache_manager
         */
        $cache_manager = App::make(CacheManager::class);

        if ($cache_manager->put('middlewares.json', json_encode($cache))) {
            $this->info('Telegram middlewares cached successfully.');
        }
        else {
            $this->error('Failed to cache Telegram middlewares!');
        }
    }
}
