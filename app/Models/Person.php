<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $table = 'persons';
    protected $primaryKey = 'pid';
    protected $guarded = ['pid'];
    public $timestamps = FALSE;

    public function scopeIsDoctor($query) {
        return $query->joinUser()
        ->joinSpecialist()
        ->whereRaw('users.lid = ?', [2]);
    }

    public function scopeJoinFullInfo($query) {
        return $query->joinGender()
                ->joinUser('left')
                ->joinReligion()
                ->joinMarriedStatus()
                ->joinTitle()
                ->joinIdentityType('left');
    }

    public function scopeJoinUser($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('users', 'users.uid', '=', 'persons.uid');
    }

    public function scopeJoinGender($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('genders', 'genders.gid', '=', 'persons.gid')
            ->selectRaw('genders.name as gender');
    }

    public function scopeJoinReligion($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('religions', 'religions.rid', '=', 'persons.rid')
            ->selectRaw('religions.name as religion');
    }

    public function scopeJoinMarriedStatus($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('married_status', 'married_status.msid', '=', 'persons.msid')
                ->selectRaw('married_status.name as married_status');
    }

    public function scopeJoinTitle($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('titles', 'titles.tid', '=', 'persons.tid')
                ->selectRaw('titles.name as title, titles.short as title_short');
    }

    public function scopeJoinIdentityType($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('identity_type', 'identity_type.itid', '=', 'persons.itid')
                ->selectRaw('identity_type.name as identity_type');
    }

    public function scopeFamilyMember($query, $pid) {
        return $query->whereRaw("(persons.pid = ? OR persons.family_id = ?) AND persons.pid <> ?", [$pid, $pid, $pid]);
    }

    public function scopeJoinSpecialist($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('specialists', 'specialists.sid', '=', 'persons.sid')
                ->selectRaw('specialists.alt_name, specialists.title');
    }

    public function scopeJoinFamily($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('family_tree', 'family_tree.fmid', '=', 'persons.fmid')
                ->$join('persons as family', 'family.pid', '=', 'family_tree.pid');
    }

    public static function PhoneUsed($phone_number) {
        return Person::where('phone_number', $phone_number)->first();
    }

    public static function phoneExist($phone) {
        $person = Person::where('phone_number',$phone)->first();
        return $person ? $person : false ;
    }
}
