<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogRadiology extends Model
{
    use HasFactory;
    protected $table = 'log_radiologies';
    protected $primaryKey = 'lrid';
    protected $guarded = ['lrid'];
    public $timestamps = FALSE;
}
