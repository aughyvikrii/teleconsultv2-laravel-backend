<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class ScheduleMap extends Model
{
    use HasFactory;

    protected $table = 'schedule_map';

    protected  $casts = [
        'monday' => 'json',
        'tuesday' => 'json',
        'wednesday' => 'json',
        'thursday' => 'json',
        'friday' => 'json',
        'saturday' => 'json',
        'sunday' => 'json'
    ];

    public function scopeMap($query) {
        return $query->join('persons as p', 'p.pid', '=', 'schedule_map.doctor_id')
                ->selectRaw('CASE WHEN schedule_map.monday IS NULL THEN \'[]\' ELSE schedule_map.monday end as monday')
                ->selectRaw('CASE WHEN schedule_map.tuesday IS NULL THEN \'[]\' ELSE schedule_map.tuesday end as tuesday')
                ->selectRaw('CASE WHEN schedule_map.wednesday IS NULL THEN \'[]\' ELSE schedule_map.wednesday end as wednesday')
                ->selectRaw('CASE WHEN schedule_map.thursday IS NULL THEN \'[]\' ELSE schedule_map.thursday end as thursday')
                ->selectRaw('CASE WHEN schedule_map.friday IS NULL THEN \'[]\' ELSE schedule_map.friday end as friday')
                ->selectRaw('CASE WHEN schedule_map.saturday IS NULL THEN \'[]\' ELSE schedule_map.saturday end as saturday')
                ->selectRaw('CASE WHEN schedule_map.sunday IS NULL THEN \'[]\' ELSE schedule_map.sunday end as sunday');
    }
}