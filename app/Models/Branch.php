<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $table = 'branches';
    protected $primaryKey = 'bid';
    protected $guarded = ['bid'];
    public $timestamps = FALSE;


    public function scopeActive($query, $active=true) {
        return $query->where('branches.is_active',$active);
    }
}
