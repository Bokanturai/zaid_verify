<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasDateFilter
{
    /**
     * Scope a query to only include results between two dates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $column
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates(Builder $query, Carbon $startDate, Carbon $endDate, string $column = 'created_at'): Builder
    {
        return $query->whereBetween($column, [$startDate, $endDate]);
    }
}
