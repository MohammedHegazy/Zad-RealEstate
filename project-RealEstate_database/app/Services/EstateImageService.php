<?php

namespace App\Services;

use App\Models\Estate;
use App\Models\EstateImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EstateImageService
{
    public function __construct(
        private readonly FileUploadService $uploader,
    ) {}

    public function createImage(Estate $estate, UploadedFile $file, bool $isPrimary = false): EstateImage
    {
        return DB::transaction(function () use ($estate, $file, $isPrimary) {
            if ($isPrimary) {
                $estate->images()->update(['is_primary' => false]);
            }

            $path = $this->uploader->storeImage($file, 'estates/'.$estate->id);

            $maxSort = $estate->images()->max('sort_order') ?? 0;

            return $estate->images()->create([
                'image' => $path,
                'is_primary' => $isPrimary,
                'sort_order' => $maxSort + 1,
            ]);
        });
    }

    public function reorderImages(Estate $estate, array $imageIds): void
    {
        DB::transaction(function () use ($estate, $imageIds) {
            foreach ($imageIds as $index => $id) {
                $estate->images()
                    ->where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateImage(EstateImage $image, array $attributes, ?UploadedFile $file = null): EstateImage
    {
        $attributes = Arr::only($attributes, ['is_primary']);

        return DB::transaction(function () use ($image, $attributes, $file) {
            if ($file !== null) {
                $this->uploader->deleteIfExists($image->image);
                $attributes['image'] = $this->uploader->storeImage(
                    $file,
                    'estates/'.$image->estate_id,
                );
            }

            if (($attributes['is_primary'] ?? false) === true) {
                EstateImage::query()
                    ->where('estate_id', $image->estate_id)
                    ->whereKeyNot($image->id)
                    ->update(['is_primary' => false]);
            }

            $image->update($attributes);

            return $image->fresh();
        });
    }

    /**
     * Mark one gallery image as the estate main image (stored as is_primary).
     */
    public function setMainImage(Estate $estate, EstateImage $image): EstateImage
    {
        if ($image->estate_id !== $estate->id) {
            throw new InvalidArgumentException('Image does not belong to this estate.');
        }

        return DB::transaction(function () use ($estate, $image) {
            $estate->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);

            return $image->fresh();
        });
    }

    public function deleteImage(EstateImage $image): void
    {
        $this->uploader->deleteIfExists($image->image);
        $image->delete();
    }

    public function formatForResponse(EstateImage $image): array
    {
        $data = $image->toArray();
        $data['image_url'] = $this->uploader->publicUrl($image->image);

        return $data;
    }
}
