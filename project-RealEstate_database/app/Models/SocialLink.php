<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialLink extends Model
{
    use HasFactory;
    protected $fillable = [
        'socialable_type',
        'socialable_id',
        'platform',
        'url',
    ];

    public function socialable(): MorphTo
    {
        return $this->morphTo();
    }
}
