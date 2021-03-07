<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $table = 'appointments';
    protected $primaryKey = 'aid';
    protected $guarded = ['aid'];
    public $timestamps = FALSE;
    protected $columns = [
        'aid', 'patient_id', 'scid', 'consul_date', 'consul_time', 'main_complaint',
        'disease_history', 'allergy', 'body_temperature', 'blood_pressure', 'weight', 'height', 'status',
        'created_at', 'create_id', 'last_update', 'delete_id', 'deleted_at', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'bool'
    ];

    public function scopeSelectForUser($query) {
        $query->selectRaw('patient.*');
        return $query->exclude([
            'appointments.is_active', 'appointments.'
        ]);
    }

    function joinType($type) {
        return ($type === 'join') ? 'join' : $type."join";
    }

    function checkJoin($query, $table) {
        $joins = $query->getQuery()->joins;
        if(!$joins) return false;
        foreach($joins as $join) {
            if($join->table == $table) return true;
        }
        return false;
    }

    public function scopeExclude($query, $value = []) {
        return $query->select(array_diff($this->columns, (array) $value));
    }

    public function scopeJoinFullInfo($query, $type = 'join') {
        return $query->joinPatient($type)
                ->JoinSchedule($type)
                ->JoinDoctor($type)
                ->joinDepartment($type)
                ->joinBranch($type);
    }

    public function scopeJoinPatient($query, $type = 'join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'persons as patient')) {
            return $query->$join('persons as patient', 'patient.pid', '=', 'appointments.patient_id');
        }
        return $query;
    }

    public function scopeJoinSchedule($query, $type = 'join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'schedules')) {
            return $query->$join('schedules', 'schedules.scid', '=', 'appointments.scid');
        }
        return $query;
    }

    public function scopeJoinDoctor($query, $type = 'join') {
        $join = self::joinType($type);

        $query->joinSchedule($type);

        if(!$this->checkJoin($query, 'persons as doctor')) {
            return $query->$join('persons as doctor', 'doctor.pid', '=', 'schedules.pid');
        }

        return $query;
    }

    public function scopeJoinDepartment($query, $type = 'join') {
        $join = self::joinType($type);

        $query->joinSchedule($type);

        if(!$this->checkJoin($query, 'departments')) {
            return $query->$join('departments', 'departments.deid', '=', 'schedules.deid');
        }

        return $query;
    }

    public function scopeJoinBranch($query, $type = 'join') {
        $join = self::joinType($type);

        $query->joinSchedule($type);

        if(!$this->checkJoin($query, 'branches')) {
            return $query->$join('branches', 'branches.bid', '=', 'schedules.bid');
        }

        return $query;
    }
}
