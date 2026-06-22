<?php

namespace Database\Factories;

use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use OverflowException;

/**
 * @extends Factory<SocialLink>
 */
class SocialLinkFactory extends Factory
{
    protected $model = SocialLink::class;

    protected bool $explicitPlatform = false;

    private static ?self $activeFactory = null;

    /**
     * Platforms reserved during the current factory create() batch (before persist).
     *
     * @var array<string, list<string>>
     */
    private static array $batchReservedPlatforms = [];

    public function definition(): array
    {
        $platform = fake()->randomElement($this->platforms());

        return [
            'socialable_type' => User::class,
            'socialable_id' => User::factory(),
            'platform' => $platform,
            'url' => $this->urlForPlatform($platform),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (SocialLink $link) {
            if ($link->socialable_type === null || $link->socialable_id === null) {
                return;
            }

            $model = (new $link->socialable_type)->setRawAttributes([
                'id' => $link->socialable_id,
            ], sync: true);

            $factory = self::$activeFactory ?? $this;

            if ($factory->explicitPlatform) {
                $key = $model->getMorphClass().':'.$model->getKey();
                self::$batchReservedPlatforms[$key][] = $link->platform;

                return;
            }

            $factory->ensureUniquePlatform($model, $link);
        });
    }

    public function create($attributes = [], ?Model $parent = null)
    {
        self::$batchReservedPlatforms = [];

        try {
            return parent::create($attributes, $parent);
        } finally {
            self::$batchReservedPlatforms = [];
            self::$activeFactory = null;
        }
    }

    public function newInstance(array $arguments = [])
    {
        $instance = parent::newInstance($arguments);
        $instance->explicitPlatform = $this->explicitPlatform;

        return $instance;
    }

    protected function makeInstance(?Model $parent)
    {
        self::$activeFactory = $this;

        return parent::makeInstance($parent);
    }

    public function forSocialable(Model $model): static
    {
        return $this->state(function (array $attributes) use ($model) {
            $platform = $attributes['platform'] ?? fake()->randomElement($this->platforms());

            return [
                'socialable_type' => $model->getMorphClass(),
                'socialable_id' => $model->getKey(),
                'platform' => $platform,
                'url' => $this->urlForPlatform($platform),
            ];
        });
    }

    public function platform(string $platform): static
    {
        $factory = $this->state(fn () => [
            'platform' => $platform,
            'url' => $this->urlForPlatform($platform),
        ]);

        $factory->explicitPlatform = true;

        return $factory;
    }

    private function ensureUniquePlatform(Model $model, SocialLink $link): void
    {
        $key = $model->getMorphClass().':'.$model->getKey();

        $used = array_merge(
            SocialLink::query()
                ->where('socialable_type', $model->getMorphClass())
                ->where('socialable_id', $model->getKey())
                ->pluck('platform')
                ->all(),
            self::$batchReservedPlatforms[$key] ?? [],
        );

        if (! in_array($link->platform, $used, true)) {
            self::$batchReservedPlatforms[$key][] = $link->platform;

            return;
        }

        $available = array_values(array_diff($this->platforms(), $used));

        if ($available === []) {
            throw new OverflowException(
                'No unused social platforms remain for '.$model->getMorphClass().' #'.$model->getKey().'.'
            );
        }

        $link->platform = fake()->randomElement($available);
        $link->url = $this->urlForPlatform($link->platform);
        self::$batchReservedPlatforms[$key][] = $link->platform;
    }

    /**
     * @return list<string>
     */
    private function platforms(): array
    {
        return config('realestate.social_platforms', [
            'facebook',
            'instagram',
            'twitter',
            'linkedin',
            'youtube',
            'tiktok',
            'whatsapp',
            'website',
        ]);
    }

    private function urlForPlatform(string $platform): string
    {
        return match ($platform) {
            'facebook' => 'https://facebook.com/'.fake()->userName(),
            'instagram' => 'https://instagram.com/'.fake()->userName(),
            'twitter' => 'https://twitter.com/'.fake()->userName(),
            'linkedin' => 'https://linkedin.com/in/'.fake()->userName(),
            'youtube' => 'https://youtube.com/@'.fake()->userName(),
            'tiktok' => 'https://tiktok.com/@'.fake()->userName(),
            'whatsapp' => 'https://wa.me/'.fake()->numerify('##########'),
            'website' => fake()->url(),
            default => fake()->url(),
        };
    }
}
