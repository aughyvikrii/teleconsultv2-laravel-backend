<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogReport extends Model
{
    use HasFactory;
    protected $table = 'log_reports';
    protected $primaryKey = 'lrid';
    protected $guarded = ['lrid'];
    public $timestamps = FALSE;
}
