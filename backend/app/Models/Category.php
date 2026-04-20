<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'color'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
