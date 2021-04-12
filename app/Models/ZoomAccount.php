<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomAccount extends Model
{
    use HasFactory;
    protected $table = 'zoom_accounts';
    protected $primaryKey = 'zaid';
    protected $guarded = ['zaid'];
    public $timestamps = FALSE;
}
