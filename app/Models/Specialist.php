<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialist extends Model
{
    use HasFactory;
    protected $table = 'specialists';
    protected $primaryKey = 'sid';
    protected $guarded = ['sid'];
    public $timestamps = FALSE;
}
