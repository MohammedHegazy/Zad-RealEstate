<?php

namespace Database\Seeders;

use App\Models\Estate;
use App\Models\EstateImage;
use App\Models\EstateVideo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class EstateMediaSeeder extends Seeder
{
    public function run(): void
    {
        $estates = Estate::all();

        if ($estates->isEmpty()) {
            $this->command?->warn('EstateMediaSeeder skipped: no estates found.');
            return;
        }

        $imageFiles = Storage::disk('public')->files('estates/seed/images');
        $videoFiles = Storage::disk('public')->files('estates/seed/videos');

        $imageFiles = array_values(array_filter($imageFiles, fn ($f) => preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)));

        if (empty($imageFiles) || empty($videoFiles)) {
            $this->command?->warn('EstateMediaSeeder skipped: no seed media files found in storage/app/public/estates/seed/.');
            return;
        }

        $this->command?->info('Found ' . count($imageFiles) . ' seed images and ' . count($videoFiles) . ' seed videos.');

        EstateImage::query()->delete();
        EstateVideo::query()->delete();

        $imageRecords = [];
        $videoRecords = [];
        $now = now();

        foreach ($estates as $estate) {
            $selectedImages = fake()->randomElements($imageFiles, 5);

            foreach ($selectedImages as $i => $path) {
                $imageRecords[] = [
                    'estate_id' => $estate->id,
                    'image' => $path,
                    'is_primary' => $i === 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $videoRecords[] = [
                'estate_id' => $estate->id,
                'video' => $videoFiles[0],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $videoRecords[] = [
                'estate_id' => $estate->id,
                'video' => $videoFiles[0],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->command?->info('Inserting ' . count($imageRecords) . ' images and ' . count($videoRecords) . ' videos...');

        foreach (array_chunk($imageRecords, 100) as $chunk) {
            EstateImage::insert($chunk);
        }

        foreach (array_chunk($videoRecords, 100) as $chunk) {
            EstateVideo::insert($chunk);
        }
    }
}
