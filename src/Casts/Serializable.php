<?php

namespace MohammadZarifiyan\Telegram\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Serializable implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return @unserialize($value) ?: $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return serialize($value);
    }
}
