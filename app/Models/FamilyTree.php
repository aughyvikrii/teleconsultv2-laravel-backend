<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyTree extends Model
{
    use HasFactory;
    protected $table = 'family_tree';
    protected $primaryKey = 'ftid';
    protected $guarded = ['ftid'];
    public $timestamps = FALSE;
}
