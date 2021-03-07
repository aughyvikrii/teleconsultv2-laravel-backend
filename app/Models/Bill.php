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

    public static function createUniq($prefix = null) {
        $uniq = null;
        while(!$uniq) {
            $random = date('ym'). rand(00,99) . rand(00,99) ;
            $exists = Bill::where('uniq', $random)->first();
            if(!$exists) $uniq = $random;
        }
        return $uniq;
    }
}
