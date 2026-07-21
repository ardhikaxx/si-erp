<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DocumentNumber
{
    public static function generate($prefix, $table, $column = 'code', $date = null)
    {
        $date = $date ?? now();
        $year = $date->format('Y');
        $month = $date->format('m');

        $lastNumber = DB::table($table)
            ->where($column, 'LIKE', "$prefix-$year-$month-%")
            ->orderBy('id', 'desc')
            ->value($column);

        if ($lastNumber) {
            $parts = explode('-', $lastNumber);
            $lastSeq = (int) end($parts);
            $seq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $seq = '0001';
        }

        return "$prefix-$year-$month-$seq";
    }

    public static function generateSimple($prefix, $table, $column = 'code')
    {
        $year = now()->format('Y');

        $lastNumber = DB::table($table)
            ->where($column, 'LIKE', "$prefix-$year-%")
            ->orderBy('id', 'desc')
            ->value($column);

        if ($lastNumber) {
            $parts = explode('-', $lastNumber);
            $lastSeq = (int) end($parts);
            $seq = str_pad($lastSeq + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $seq = '00001';
        }

        return "$prefix-$year-$seq";
    }
}
