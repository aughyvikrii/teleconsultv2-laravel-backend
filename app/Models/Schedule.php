<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules';
    protected $primaryKey = 'scid';
    protected $guarded = ['scid'];
    public $timestamps = FALSE;

    public function scopeJoinFullInfo($query, $type = 'join') {
        return $query->joinPerson($type)
                ->joinBranch($type)
                ->joinDepartment($type)
                ->joinCreator($type)
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
}
