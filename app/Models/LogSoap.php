<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogSoap extends Model
{
    use HasFactory;
    protected $table = 'log_soaps';
    protected $primaryKey = 'lsid';
    protected $guarded = ['lsid'];
    public $timestamps = FALSE;
}
