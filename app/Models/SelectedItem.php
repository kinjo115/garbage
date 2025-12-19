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
        'reception_number_serial',
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

    /**
     * 受付番号を取得（YYMM-00001形式）
     */
    public function getReceptionNumberAttribute(): ?string
    {
        if (!$this->reception_number_serial || !$this->payment_date) {
            return null;
        }

        $paymentDate = \Carbon\Carbon::parse($this->payment_date);
        $yy = $paymentDate->format('y'); // 2桁の年
        $mm = $paymentDate->format('m'); // 2桁の月
        $serial = str_pad($this->reception_number_serial, 5, '0', STR_PAD_LEFT);

        return $yy . $mm . '-' . $serial;
    }
}
