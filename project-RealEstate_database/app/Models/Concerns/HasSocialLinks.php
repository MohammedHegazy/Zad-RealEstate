<?php

namespace App\Models\Concerns;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSocialLinks
{
    public static function bootHasSocialLinks(): void
    {
        static::deleting(function (Model $model): void {
            $model->socialLinks()->delete();
        });
    }

    public function socialLinks(): MorphMany
    {
        return $this->morphMany(SocialLink::class, 'socialable');
    }
}
