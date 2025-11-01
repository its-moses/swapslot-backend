<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventsTbl extends Model
{
    protected $table = 'eventstbl';
    public $timestamps = false;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function sentSwapRequests()
    {
        return $this->hasMany(SwapRequest::class, 'mySlotId');
    }

    public function receivedSwapRequests()
    {
        return $this->hasMany(SwapRequest::class, 'theirSlotId');
    }
}
