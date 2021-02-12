<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMaster extends Model
{
    use HasFactory;
    protected $table = 'family_master';
    protected $primaryKey = 'fmid';
    protected $guarded = ['fmid'];
    public $timestamps = FALSE;
}
