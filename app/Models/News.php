<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;
    protected $table = 'news';
    protected $primaryKey = 'nid';
    protected $guarded = ['nid'];
    public $timestamps = FALSE;

    public function scopeJoinCreator($query) {
        return $query->join('users', 'users.uid', '=', 'news.create_id')
                ->join('persons as creator', 'creator.uid', '=', 'users.uid');
    }
}
