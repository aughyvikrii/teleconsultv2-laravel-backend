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
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'users')) {
            $query->$join('users', 'users.uid', '=', 'persons.uid');
        }
        return $query;
    }

    public function scopeJoinGender($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'genders')) {
            $query->$join('genders', 'genders.gid', '=', 'persons.gid')
            ->selectRaw('genders.name as gender');
        }
        return $query;
    }

    public function scopeJoinReligion($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'religions')) {
            $query->$join('religions', 'religions.rid', '=', 'persons.rid')
                ->selectRaw('religions.name as religion');
        }
        return $query;
    }

    public function scopeJoinMarriedStatus($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'married_status')) {
            $query->$join('married_status', 'married_status.msid', '=', 'persons.msid')
                    ->selectRaw('married_status.name as married_status');
        }
        return $query;
    }

    public function scopeJoinTitle($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'titles')) {
            $query->$join('titles', 'titles.tid', '=', 'persons.tid')
                    ->selectRaw('titles.name as title, titles.short as title_short');
        }
        return $query;
    }

    public function scopeJoinIdentityType($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'identity_type')) {
            $query->$join('identity_type', 'identity_type.itid', '=', 'persons.itid')
                    ->selectRaw('identity_type.name as identity_type');
        }
        return $query;
    }

    public function scopeFamilyMember($query, $pid) {
        return $query->whereRaw("(persons.pid = ? OR persons.family_id = ?) AND persons.pid <> ?", [$pid, $pid, $pid]);
    }

    public function scopeJoinSpecialist($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'specialists')) {
            $query->$join('specialists', 'specialists.sid', '=', 'persons.sid')
                    ->selectRaw('specialists.alt_name, specialists.title');
        }
        return $query;
    }

    public function scopeJoinFamily($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'family_tree')) {
            $query->$join('family_tree', 'family_tree.fmid', '=', 'persons.fmid');

            if(!$this->checkJoin($query, 'persons as family')) {
                $query->$join('persons as family', 'family.pid', '=', 'family_tree.pid');
            }
        }
        return $query;
    }

    public function scopeJoinVillage($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'villages')) {
            $query->$join('villages', 'villages.vid', '=', 'persons.vid');
        }
        
        $query->JoinDistrict()->JoinCity()->JoinProvince()->JoinIsland();

        return $query;
    }

    public function scopeJoinIsland($query, $type = 'join') {
        $join = self::joinType($type);
        if(!$this->CheckJoin($query, 'islands')) {
            $query->$join('islands', 'islands.iid', '=', 'provinces.iid' );
        }

        return $query;
    }

    public function scopeJoinDistrict($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'districts')) {
            $query->$join('districts', 'districts.did', '=', 'villages.did');
        }
        return $query;
    }

    public function scopeJoinCity($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'cities')) {
            $query->$join('cities', 'districts.cid', '=', 'cities.cid');
        }
        return $query;
    }

    public function scopeJoinProvince($query, $type='join') {
        $join = self::joinType($type);
        if(!$this->checkJoin($query, 'provinces')) {
            $query->$join('provinces', 'provinces.pvid', '=', 'cities.pvid');
        }
        return $query;
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

    public function scopeJoinZoomAccount($query, $type = 'join') {
        $join = self::JoinType($type);
        if(!$this->checkJoin($query, 'zoom_accounts')) {
            $query->$join('zoom_accounts', 'zoom_accounts.pid', '=', 'persons.pid');
        }

        return $query;
    }

    public function scopePatient($query) {
        return $query->JoinUser('left')
                ->whereRaw("(users.lid = 3 OR persons.uid IS NULL)");
    }
}
