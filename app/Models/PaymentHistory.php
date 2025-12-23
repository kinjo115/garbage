<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentHistory extends Model
{
    protected $fillable = [
        'selected_item_id',
        'shop_id',
        'access_id',
        'order_id',
        'status',
        'job_cd',
        'amount',
        'tax',
        'currency',
        'forward',
        'method',
        'pay_times',
        'tran_id',
        'approve',
        'tran_date',
        'err_code',
        'err_info',
        'pay_type',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'amount' => 'integer',
        'tax' => 'integer',
        'pay_times' => 'integer',
    ];

    /**
     * Get the selected item that owns this payment history
     */
    public function selectedItem(): BelongsTo
    {
        return $this->belongsTo(SelectedItem::class);
    }
}
