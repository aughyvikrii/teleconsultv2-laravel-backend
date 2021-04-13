<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $table = 'bills';
    protected $primaryKey = 'blid';
    protected $guarded = ['blid'];
    public $timestamps = FALSE;

    protected $casts = [
        'midtrans_pending_raw' => 'json',
        'midtrans_paid_raw' => 'json',
        'midtrans_last_raw' => 'json',
        'midtrans_paid_log' => 'json',
        'midtrans_pending_log' => 'json',
        'midtrans_log' => 'json'
    ];

    public static function createUniq($prefix = null) {
        $uniq = null;
        while(!$uniq) {
            $random = date('ym'). rand(00,99) . rand(00,99) ;
            $exists = Bill::where('uniq', $random)->first();
            if(!$exists) $uniq = $random;
        }
        return $uniq;
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

    public function scopeJoinFullInfo($query, $type='join') {
        return $query->JoinAppointment($type)
                ->JoinPatient($type)
                ->JoinDoctor($type)
                ->JoinDepartment($type)
                ->JoinBranch($type);
    }

    public function scopeJoinAppointment($query, $type = 'join') {
        $join = self::joinType($type);

        if(!$this->checkJoin($query, 'appointments')) {
            $query->$join('appointments', 'appointments.aid', '=', 'bills.aid');
        }

        return $query;
    }
    
    public function scopeJoinPatient($query, $type = 'join') {
        $join = self::joinType($type);
        
        if(!$this->checkJoin($query,'persons as patient')) {
            $query->JoinAppointment()
            ->$join('persons as patient', 'patient.pid', '=', 'appointments.patient_id');
        }

        return $query;
    }

    public function scopeJoinSchedule($query, $type = 'join') {
        $join = self::joinType($type);
        
        if(!$this->checkJoin($query,'schedules')) {
            $query->JoinAppointment()
            ->$join('schedules', 'schedules.scid', '=', 'appointments.scid');
        }

        return $query;
    }

    public function scopeJoinDoctor($query, $type = 'join') {
        $join = self::joinType($type);
        
        if(!$this->checkJoin($query,'persons as doctor')) {
            $query->JoinSchedule()
            ->$join('persons as doctor', 'doctor.pid', '=', 'schedules.pid');
        }

        return $query;
    }

    public function scopeJoinDepartment($query, $type = 'join') {
        $join = self::joinType($type);
        
        if(!$this->checkJoin($query,'departments')) {
            $query->JoinSchedule()
            ->$join('departments', 'departments.deid', '=', 'schedules.deid');
        }

        return $query;
    }

    public function scopeJoinBranch($query, $type = 'join') {
        $join = self::joinType($type);
        
        if(!$this->checkJoin($query,'branches')) {
            $query->JoinSchedule()
            ->$join('branches', 'branches.bid', '=', 'schedules.bid');
        }

        return $query;
    }
}
