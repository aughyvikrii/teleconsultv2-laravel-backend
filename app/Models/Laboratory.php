<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    use HasFactory;
    protected $table = 'laboratories';
    protected $primaryKey = 'labid';
    protected $guarded = ['labid'];
    public $timestamps = FALSE;
}
