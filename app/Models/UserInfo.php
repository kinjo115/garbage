<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInfo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'temp_user_id',
        'last_name',
        'first_name',
        'housing_type_id',
        'postal_code',
        'prefecture_id',
        'city',
        'town',
        'chome',
        'building_number',
        'house_number',
        'building_name',
        'apartment_name',
        'apartment_number',
        'phone_number',
        'emergency_contact',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function housingType()
    {
        return $this->belongsTo(HousingType::class);
    }

    public function prefecture()
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function tempUser()
    {
        return $this->belongsTo(TempUser::class);
    }
}
