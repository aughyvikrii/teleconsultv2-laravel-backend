<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $table = 'departments';
    protected $primaryKey = 'deid';
    protected $guarded = ['deid'];
    public $timestamps = FALSE;

    public function scopeActive($query, $active=true) {
        return $query->where('departments.is_active',$active);
    }
}
