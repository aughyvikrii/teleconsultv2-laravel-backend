<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Radiology extends Model
{
    use HasFactory;
    protected $table = 'radiologies';
    protected $primaryKey = 'radid';
    protected $guarded = ['radid'];
    public $timestamps = FALSE;
}
