<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    protected $table = 'doctors';
    protected $primaryKey = 'doid';
    protected $guarded = ['doid'];
    public $timestamps = FALSE;

    public function scopeJoinPerson($query) {
        return $query->join('persons', 'persons.pid', '=', 'doctors.pid');
    }

    public function scopeJoinBranch($query) {
        return $query->join('branches', 'branches.bid', '=', 'doctors.bid');
    }

    public function scopeJoinDepartement($query) {
        return $query->join('departements', 'departements.deid', '=', 'doctors.deid');
    }

    public function scopeJoinSpecialist($query) {
        return $query->join('specialists', 'specialists.sid', '=', 'doctors.sid');
    }

    public function scopeJoinUser($query) {
        return $query->join('users', 'users.uid', '=', 'persons.uid');
    }

    public function scopeGetFullInfo($query) {
        return $query->joinPerson()
                ->joinUser()
                ->joinBranch()
                ->joinDepartement()
                ->joinSpecialist()
                ->selectRaw('doctors.doid, doctors.fee_consultation')
                ->selectRaw('persons.pid, persons.full_name as name')
                ->selectRaw('departements.deid, departements.name as departement')
                ->selectRaw('branches.bid, branches.name as branch')
                ->selectRaw('specialists.sid, specialists.title, specialists.alt_name as title_name')
                ->selectRaw('doctors.is_active');
    }

    public function scopeGetFullInfoByDoid($query, $doid) {
        return $query->getFullInfo()
            ->where('doctors.doid', $doid)
            ->first();
    }
}
