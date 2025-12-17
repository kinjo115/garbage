<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'parent_id',
        'sort',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'item_category_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(ItemCategory::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(ItemCategory::class, 'parent_id', 'id');
    }
}
