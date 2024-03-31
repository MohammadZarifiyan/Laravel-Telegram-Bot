<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

interface CacheManager
{
    /**
     * @throw \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @param string $filename
     * @return string|null
     */
    public function get(string $filename): ?string;

    public function exists(string $filename): bool;

    public function put(string $filename, string $content): bool;

    public function delete(string $filename): bool;
}
