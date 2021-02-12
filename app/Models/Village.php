<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;
    protected $table = 'villages';
    protected $primaryKey = 'vid';
    protected $guarded = ['vid'];
    public $timestamps = FALSE;


    public function scopeFullJoin($query, $type='join') {
        return $query->joinDistrict($type)
                ->joinCities($type)
                ->joinProvinces($type);
    } 

    public function scopejoinDistrict($query, $type='join'){
        $join = self::joinType($type);
        return $query->$join('districts', 'districts.did', '=', 'villages.did');
    }

    public function scopeJoinCities($query, $type='join'){
        $join = self::joinType($type);
        return $query->$join('cities', 'cities.cid', '=', 'districts.cid');
    }

    public function scopeJoinProvinces($query, $type='join'){
        $join = self::joinType($type);
        return $query->$join('provinces', 'provinces.pvid', '=', 'cities.pvid');
    }

    function joinType($type) {
        return ($type === 'join') ? 'join' : $type."join";
    }
}
