<?php

namespace MohammadZarifiyan\Telegram\Interfaces;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Update;

interface Telegram
{
    public function setApiKey(string $apiKey): static;

    public function setEndpoint(string $endpoint): static;

    public function handleRequest(Request $request): void;

    public function getUpdate(): ?Update;

    public function perform(string $method, array $data = [], ReplyMarkup|string|null $replyMarkup = null): Response;

    public function async(Closure $closure): array;

    public function generateFileUrl(string $filePath): string;
}