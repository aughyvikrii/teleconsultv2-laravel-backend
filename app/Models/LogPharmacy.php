<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPharmacy extends Model
{
    use HasFactory;
    protected $table = 'log_pharmacies';
    protected $primaryKey = 'lpid';
    protected $guarded = ['lpid'];
    public $timestamps = FALSE;
}
