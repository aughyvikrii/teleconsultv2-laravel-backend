<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soap extends Model
{
    use HasFactory;
    protected $table = 'soaps';
    protected $primaryKey = 'soid';
    protected $guarded = ['soid'];
    public $timestamps = FALSE;
}
