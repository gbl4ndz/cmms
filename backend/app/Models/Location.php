<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'address', 'phone', 'email'];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
