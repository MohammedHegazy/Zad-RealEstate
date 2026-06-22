<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstateVideo extends Model
{
    protected $table = 'estate_videos';

    protected $fillable = [
        'estate_id',
        'video',
    ];

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }
}
