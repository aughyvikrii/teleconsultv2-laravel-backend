<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarriedStatus extends Model
{
    use HasFactory;

    protected $table = 'married_status';
    protected $primaryKey = 'msid';
    protected $guarded = ['msid'];
    public $timestamps = FALSE;
}
