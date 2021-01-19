<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    
    protected $table = 'users';
    protected $primaryKey = 'uid';
    protected $guarded = ['uid'];
    public $timestamps = FALSE;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function emailExist($email) {
        return User::where('email',$email)->first() ? true : false ;
    }

    public static function phoneExist($phone) {
        return User::where('phone_number',$phone)->first() ? true : false ;
    }

    public static function login($email, $password, $remember_me = false) {
        $user = User::join('level', 'level.lid','=','users.lid')
                ->selectRaw('users.*, level.code as lid_code')
                ->where('users.email', $email)
                ->first();
        if(!$user) return false;
        else if(!Hash::check($password, $user->password)) return false;
        else return $user;
    }

    public function scopeFullinfo($query) {
        return $query->leftjoin('persons', 'persons.uid', '=', 'users.uid')
            ->leftjoin('level', 'level.lid', '=', 'persons.lid')
            ->selectRaw('users.*, level.name as user_level')
            ->selectRaw('persons.*');
    }
}
