<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomMeeting extends Model
{
    use HasFactory;
    protected $table = 'zoom_meetings';
    protected $guarded = ['zmid'];
    protected $primaryKey = 'zmid';

    public function setRawDataAttribute($value) {
        return $this->attributes['raw_data'] = json_encode(@$value ? $value : []);
    }
}
