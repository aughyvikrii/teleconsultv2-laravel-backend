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
        'midtrans_last_raw' => 'json'
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
}
