<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogLaboratory extends Model
{
    use HasFactory;
    protected $table = 'log_laboratories';
    protected $primaryKey = 'llid';
    protected $guarded = ['llid'];
    public $timestamps = FALSE;
}
