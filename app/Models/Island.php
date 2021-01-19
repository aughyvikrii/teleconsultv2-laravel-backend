<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Island extends Model
{
    use HasFactory;
    protected $table = 'islands';
    protected $primaryKey = 'iid';
    protected $guarded = ['iid'];
    public $timestamps = FALSE;
}
