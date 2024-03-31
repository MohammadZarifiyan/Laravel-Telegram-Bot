<?php

namespace MohammadZarifiyan\Telegram\Repositories;

use Illuminate\Support\Facades\File;
use MohammadZarifiyan\Telegram\Interfaces\CacheManager as CacheManagerInterface;

class CacheManager implements CacheManagerInterface
{
    public string $directory;

    public function __construct()
    {
        $this->directory = $this->getDirectory();
    }

    public function get(string $filename): ?string
    {
        return File::get($this->directory . '/' . $filename);
    }

    public function exists(string $filename): bool
    {
        return File::exists($this->directory . '/' . $filename);
    }

    public function put(string $filename, string $content): bool
    {
        if (!File::isDirectory($this->directory)) {
            File::ensureDirectoryExists($this->directory);

            $this->updateGitIgnore();
        }

        return File::exists($this->directory . '/' . $filename);
    }

    public function delete(string $filename): bool
    {
        return File::delete($this->directory . '/' . $filename);
    }

    public function updateGitIgnore(): void
    {
        File::put($this->directory . '/.gitignore', <<<TEXT
*
!.gitignore
TEXT
        );
    }

    public function getDirectory(): string
    {
        $storage_directory = str_replace(base_path(), '', storage_path());

        return trim($storage_directory, '/') . '/framework/telegram';
    }
}
