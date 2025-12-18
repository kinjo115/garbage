<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelectedItem extends Model
{
    use SoftDeletes;

    const CONFIRM_STATUS_NOT_CONFIRMED = 0;
    const CONFIRM_STATUS_CONFIRMED = 1;

    protected $fillable = [
        'temp_user_id',
        'user_id',
        'selected_items',
        'total_quantity',
        'total_amount',
        'collection_date',
        'confirm_status',
    ];

    protected $casts = [
        'selected_items' => 'array',
        'collection_date' => 'date',
    ];

    public function tempUser()
    {
        return $this->belongsTo(TempUser::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}