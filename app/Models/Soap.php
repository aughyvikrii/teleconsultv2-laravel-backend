<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soap extends Model
{
    use HasFactory;
    protected $table = 'soaps';
    protected $primaryKey = 'soid';
    protected $guarded = ['soid'];
    public $timestamps = FALSE;

    protected $casts = [
        'soap_json' => 'json',
        'appointment_json' => 'json',
        'laboratory_json' => 'json',
        'radiology_json' => 'json',
        'pharmacy_json' => 'json'
    ];

    function joinType($type) {
        return ($type === 'join') ? 'join' : $type."join";
    }

    function scopeCheckJoin($query, $table) {
        $joins = $query->getQuery()->joins;
        if(!$joins) return false;
        foreach($joins as $join) {
            if($join->table == $table) return true;
        }
        return false;
    }

    public function scopeJoinFullInfo($query) {
        $query->JoinAppointment()
            ->JoinLaboratory()
            ->JoinRadiology()
            ->JoinPharmacy();
    }

    public function scopeJoinAppointment($query) {
        if(!$this->checkJoin('appointments')) {
            $query->join('appointments', 'appointments.aid', '=', 'soaps.aid');
        }

        return $query;
    }

    public function scopeJoinLaboratory($query) {
        if(!$this->checkJoin('laboratories')) {
            $query->leftjoin('laboratories', 'laboratories.aid', '=', 'soaps.aid');
        }

        return $query;
    }

    public function scopeJoinRadiology($query) {
        if(!$this->checkJoin('radiologies')) {
            $query->leftjoin('radiologies', 'radiologies.aid', '=', 'soaps.aid');
        }

        return $query;
    }

    public function scopeJoinPharmacy($query) {
        if(!$this->checkJoin('pharmacies')) {
            $query->leftjoin('pharmacies', 'pharmacies.aid', '=', 'soaps.aid');
        }

        return $query;
    }

    public function scopeJoinFullInfoJson($query) {
        return $query->JoinFullInfo()
                ->selectRaw("
                    row_to_json(soaps) as soap_json,
                    row_to_json(appointments) as appointment_json,
                    row_to_json(laboratories) as laboratory_json,
                    row_to_json(radiologies) as radiology_json,
                    row_to_json(pharmacies) as pharmacy_json
                ");
    }
}
