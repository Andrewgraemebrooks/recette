<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;
    use UsesUuid;

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('amount');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
