<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

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
                ->joinIdentityType('left')
                ->joinVillage('left')
                ->joinDistrict('left')
                ->joinCity('left')
                ->joinProvince('left');
    }

    public function scopeDoctor($query)  {
        return $this->scopeIsDoctor($query);
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

    public function scopeJoinVillage($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('villages', 'villages.vid', '=', 'persons.vid');
    }

    public function scopeJoinDistrict($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('districts', 'districts.did', '=', 'villages.did');
    }

    public function scopeJoinCity($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('cities', 'districts.cid', '=', 'cities.cid');
    }

    public function scopeJoinProvince($query, $type='join') {
        $join = ($type == 'join') ? 'join' : "{$type}join";
        return $query->$join('provinces', 'provinces.pvid', '=', 'cities.pvid');
    }

    public static function PhoneUsed($phone_number) {
        return Person::where('phone_number', $phone_number)->first();
    }

    public static function phoneExist($phone) {
        $person = Person::where('phone_number',$phone)->first();
        return $person ? $person : false ;
    }

    public static function getByPhone($phone, $all_status = false) {
        $where = "";
        if(!$all_status) $where .= "AND is_active = TRUE";
        $person = Person::whereRaw("phone_number = ? $where", [$phone]);

        return $all_status ? $person->get() : $person->first();
    }

    public function scopeGetFamily($query, $user_id = null) {
        if(!$user_id) $user_id = auth()->user()->uid;
        return $query->join('persons as fam', 'fam.fmid', '=', 'persons.fmid')
                ->whereRaw('persons.uid = ?', [$user_id]);
    }

    public static function FamilyMember($pid, $user_id = null) {
        if(!$user_id) $user_id = Auth::user()->uid;
        $person = Person::join('persons as fam', 'fam.fmid', '=', 'persons.fmid')
                    ->whereRaw('persons.uid = ? AND fam.pid = ?', [$user_id, $pid])
                    ->first();

        return $person;
    }

    public static function addFamily($data, $person_id=null) {
        $fmid = null;
        if(!$person_id) {
            $person = Person::where('uid', Auth::user()->uid)->first();
            if(!$person) {
                return [null, 'Parent user not found'];
            }
            $person_id = $person->pid;
            $fmid = $person->fmid;
        }

        if(!$fmid) {
            $person = Person::find($person_id);
            if(!$person) {
                return [null, 'Parent user not found'];
            }
            $fmid = $person->fmid;
        }

        $data['fmid'] = $fmid;
        $data['create_id'] = Auth::user()->uid;

        return Person::create($data);
    }

    public function scopePatient($query) {
        return $query->JoinUser('left')
                ->whereRaw("(users.lid = 3 OR persons.uid IS NULL)");
    }
}
