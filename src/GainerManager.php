<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Interfaces\GainerResolver;
use MohammadZarifiyan\Telegram\Interfaces\GainerManager as GainerManagerInterface;

class GainerManager implements GainerManagerInterface
{
    protected int|string $cachedUpdateId;
    protected mixed $gainer;

    public function getCachedGainer(Update $update): mixed
    {
        if (isset($this->cachedUpdateId, $this->gainer) && $this->cachedUpdateId === $update->input('update_id')) {
            return $this->gainer;
        }

        /**
         * @var GainerResolver $gainerResolver
         */
        $gainerResolver = App::make(GainerResolver::class);
        $this->cachedUpdateId = $update->input('update_id');
        $this->gainer = is_null($gainerResolver) ? null : $gainerResolver($update);

        return $this->gainer;
    }
}
