<?php

namespace App\Services;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class SocialLinkService
{
    /**
     * @param  array<int, array{platform: string, url: string}>  $links
     */
    public function syncLinks(Model $model, array $links, bool $replace = false): void
    {
        if ($replace) {
            $model->socialLinks()->delete();
        }

        foreach ($links as $link) {
            $platform = (string) Arr::get($link, 'platform');
            $url = (string) Arr::get($link, 'url');

            if ($platform === '' || $url === '') {
                continue;
            }

            $model->socialLinks()->updateOrCreate(
                ['platform' => $platform],
                ['url' => $url],
            );
        }
    }

    /**
     * Backward-compatible: map facebook / instagram request fields to platform rows.
     *
     * @param  array<string, mixed>  $input
     */
    public function syncLegacyFields(Model $model, array $input, bool $replace = false): void
    {
        $links = [];

        foreach (['facebook', 'instagram'] as $platform) {
            if (! array_key_exists($platform, $input)) {
                continue;
            }

            $url = $input[$platform];
            if ($url === null || $url === '') {
                if ($replace) {
                    $model->socialLinks()->where('platform', $platform)->delete();
                }
                continue;
            }

            $links[] = ['platform' => $platform, 'url' => (string) $url];
        }

        if ($links !== []) {
            $this->syncLinks($model, $links, $replace);
        }
    }

    /**
     * @param  array<string, mixed>  $input  links[] or legacy facebook/instagram
     */
    public function syncFromRequest(Model $model, array $input, bool $replace = false): void
    {
        if (isset($input['links']) && is_array($input['links'])) {
            $this->assertUniquePlatforms($input['links']);
            $this->syncLinks($model, $input['links'], $replace);

            return;
        }

        $this->syncLegacyFields($model, $input, $replace);
    }

    /**
     * @param  array<int, array<string, mixed>>  $links
     */
    public function assertUniquePlatforms(array $links): void
    {
        $platforms = array_map(fn ($link) => $link['platform'] ?? null, $links);
        $platforms = array_filter($platforms, fn ($p) => $p !== null && $p !== '');

        if (count($platforms) !== count(array_unique($platforms))) {
            throw ValidationException::withMessages([
                'links' => ['Duplicate platform entries are not allowed.'],
            ]);
        }
    }

    public function assertPlatformAvailable(Model $model, string $platform, ?int $exceptId = null): void
    {
        $query = $model->socialLinks()->where('platform', $platform);

        if ($exceptId !== null) {
            $query->whereKeyNot($exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'platform' => ["This platform already exists for this {$this->entityLabel($model)}."],
            ]);
        }
    }

    private function entityLabel(Model $model): string
    {
        return strtolower(class_basename($model));
    }

    /**
     * @return list<array{id: int, platform: string, url: string}>
     */
    public function formatCollection(Model $model): array
    {
        return $model->socialLinks()
            ->orderBy('platform')
            ->get(['id', 'platform', 'url'])
            ->map(fn (SocialLink $link) => [
                'id' => $link->id,
                'platform' => $link->platform,
                'url' => $link->url,
            ])
            ->values()
            ->all();
    }
}
