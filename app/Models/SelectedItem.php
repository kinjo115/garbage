<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelectedItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'temp_user_id',
        'user_id',
        'selected_items',
        'total_quantity',
        'total_amount',
    ];

    protected $casts = [
        'selected_items' => 'array',
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
