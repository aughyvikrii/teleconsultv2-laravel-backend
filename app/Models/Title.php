<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    use HasFactory;

    protected $table = 'titles';
    protected $primaryKey = 'tid';
    protected $guarded = ['tid'];
    public $timestamps = FALSE;
}
