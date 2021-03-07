<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules';
    protected $primaryKey = 'scid';
    protected $guarded = ['scid'];
    public $timestamps = FALSE;
    protected  $casts = [
        'list_schedule' => 'json',
        'schedule' => 'json',
        'monday' => 'json',
        'tuesday' => 'json',
        'wednesday' => 'json',
        'thursday' => 'json',
        'friday' => 'json',
        'saturday' => 'json',
        'sunday' => 'json'
    ];

    public function scopeJoinFullInfo($query, $type = 'join', $joinCreator=true) {
        if($joinCreator) $query->joinCreator($type);

        return $query->joinPerson($type)
                ->joinBranch($type)
                ->joinDepartment($type)
                ->joinSpesialist($type)
                ->joinUser($type);
    }

    function joinType($type) {
        return ($type === 'join') ? 'join' : $type."join";
    }

    public function scopeJoinSpesialist($query, $type = 'join') {
        $join = self::joinType($type);
        return $query->$join('specialists', 'specialists.sid', '=', 'persons.sid');
    }

    public function scopeJoinPerson($query, $type='join') {
        $join = self::joinType($type);
        return $query->$join('persons', 'persons.pid', '=', 'schedules.pid');
    }

    public function scopeJoinBranch($query, $type='join') {
        $join = self::joinType($type);
        return $query->$join('branches', 'branches.bid', '=', 'schedules.bid');
    }

    public function scopeJoinDepartment($query, $type='join') {
        $join = self::joinType($type);
        return $query->$join('departments', 'departments.deid', '=', 'schedules.deid');
    }

    public function scopeJoinUser($query, $type='join') {
        $join = self::joinType($type);
        return $query->$join('users', 'users.uid', '=', 'persons.uid');
    }

    public function scopeJoinCreator($query, $type='join') {
        $join = self::joinType($type);
        return $query->$join('users as _creator_user', '_creator_user.uid', '=', 'schedules.create_id')
                ->$join('persons as _creator_person', '_creator_person.uid', '=', '_creator_user.uid')
                ->selectRaw('_creator_person.full_name as creator');
    }

    public function scopeallActive($query) {
        $joins = $query->getQuery()->joins;
        if(@count($joins) > 0) foreach($joins as $join) {
            if(preg_match("/ as /", strtolower($join->table) )) {
                dd("ALIAS TABLE");
            } else {
                $table = $join->table;
            }

            $query->where("$table.is_active", true);
        }

        return $query;
    }

    public function scopeGroupingSchedule($query) {
        return $query->JoinFullInfo()
        ->selectRaw('persons.pid as doctor_id, persons.display_name as name, departments.deid as department_id, departments.name as department
        , branches.bid as branch_id, branches.name as branch, doctor_pic(persons.profile_pic) as profile_pic, min(schedules.fee) fee_min, max(schedules.fee) as fee_max')
        ->selectRaw("json_agg(
            json_build_object(
                'schedule_id', schedules.scid,
                'weekday', schedules.weekday,
                'id_weekday', id_weekday(schedules.weekday),
                'start_time', schedules.start_hour,
                'end_time', schedules.end_hour
            )
        )  as list_schedule")
        ->groupBy(DB::Raw("_creator_person.full_name, persons.pid, persons.display_name, departments.deid, departments.name
        , branches.bid, branches.name"));
    }

    public function scopeScheduleGroup($query) {
        return $query->join('v_schedule_json_list as vsjl', function($join) {
            $join->on('schedules.pid', '=', 'vsjl.doctor_id')
                ->on('schedules.deid', '=', 'vsjl.deid')
                ->on('schedules.bid', '=', 'vsjl.bid');
        })
        ->selectRaw("vsjl.doctor_id, vsjl.bid, vsjl.deid, vsjl.schedule::text")
        ->groupBy(DB::raw("vsjl.doctor_id, vsjl.bid, vsjl.deid, vsjl.schedule::text"));
    }

    public static function apiScheduleDetailByScid($scid) {
        return Schedule::joinFullInfo('join', false)
                    ->selectRaw('schedules.scid as schedule_id, schedules.weekday, id_weekday(schedules.weekday) as id_weekday,
                    schedules.duration, schedules.fee, schedules.start_hour, schedules.end_hour, persons.display_name as doctor, persons.pid as doctor_id,
                    branches.bid as branch_id, branches.name as branch, departments.deid as department_id, departments.name as department,
                    doctor_pic(persons.profile_pic) as doctor_pic')
                    ->whereRaw('schedules.scid = ? AND schedules.is_active = TRUE', [$scid])
                    ->first();
    }
}
