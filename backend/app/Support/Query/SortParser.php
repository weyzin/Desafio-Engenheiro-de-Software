<?php

namespace App\Support\Query;

class SortParser
{
    /**
     * Converte "price,-year" para [['price','asc'],['year','desc']]
     * Restringe aos campos permitidos para evitar SQL injection.
     */
    public static function parse(?string $sort, array $allowed = []): array
    {
        if (!$sort) return [];
        $parts = array_filter(explode(',', $sort));
        $orders = [];

        foreach ($parts as $p) {
            $dir = str_starts_with($p, '-') ? 'desc' : 'asc';
            $col = ltrim($p, '-');
            if (in_array($col, $allowed, true)) {
                $orders[] = [$col, $dir];
            }
        }
        return $orders;
    }
}
