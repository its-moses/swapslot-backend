<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwapRequest extends Model
{
    //
    protected $table = 'swaprequeststbl';
    protected $guarded = [];
    public function requester()
    {
        return $this->belongsTo(AuthUser::class, 'requester_user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(AuthUser::class, 'receiver_user_id');
    }

    public function mySlot()
    {
        return $this->belongsTo(EventsTbl::class, 'mySlotId');
    }

    public function theirSlot()
    {
        return $this->belongsTo(EventsTbl::class, 'theirSlotId');
    }
}
