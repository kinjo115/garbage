<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TempUser extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }
}