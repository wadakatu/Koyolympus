<?php

declare(strict_types=1);

namespace App\Models\Traits;

use DateTimeInterface;

trait DateFormat
{
    /**
     * 配列／JSONシリアライズのためデータを準備する
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
