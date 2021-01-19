<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityType extends Model
{
    use HasFactory;

    protected $table = 'identity_type';
    protected $primaryKey = 'itid';
    protected $guarded = ['itid'];
    public $timestamps = FALSE;
}
