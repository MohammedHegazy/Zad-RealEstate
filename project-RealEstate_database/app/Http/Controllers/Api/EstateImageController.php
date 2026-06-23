<?php
/*
|--------------------------------------------------------------------------
| EstateImageController — معرض صور العقار
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ManagesEstateImages;
use App\Http\Requests\StoreEstateImageRequest;
use App\Models\Estate;
use App\Models\EstateImage;
use App\Services\EstateImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class EstateImageController extends BaseApiController
{
    use ManagesEstateImages;

    public function __construct(
        private readonly EstateImageService $images,
    ) {}

    public function index(Request $request, Estate $estate): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $images = $estate->images()->orderBy('sort_order')->orderBy('id')->get()->map(fn (EstateImage $img) => $this->formatEstateImage($img, $this->images));

        return $this->successResponse($images, 'Estate images retrieved.');
    }

    public function store(StoreEstateImageRequest $request, Estate $estate): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $image = $this->images->createImage(
            $estate,
            $request->file('image'),
            $request->boolean('is_primary'),
        );

        return $this->createdResponse(
            $this->formatEstateImage($image, $this->images),
            'Image uploaded successfully.',
        );
    }

    public function setPrimary(Request $request, Estate $estate, EstateImage $image): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        try {
            $image = $this->images->setMainImage($estate, $image);
        } catch (InvalidArgumentException) {
            return $this->notFoundResponse('Image does not belong to this estate.');
        }

        return $this->successResponse(
            $this->formatEstateImage($image, $this->images),
            'Primary image updated.',
        );
    }

    public function reorder(Request $request, Estate $estate): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $request->validate([
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['integer', 'exists:estate_images,id'],
        ]);

        $this->images->reorderImages($estate, $request->input('image_ids'));

        return $this->successResponse(null, 'Images reordered successfully.');
    }

    public function destroy(Request $request, Estate $estate, EstateImage $image): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        if ($image->estate_id !== $estate->id) {
            return $this->notFoundResponse('Image does not belong to this estate.');
        }

        $this->images->deleteImage($image);

        return $this->deletedResponse('Image deleted successfully.');
    }

    private function isOwner(Request $request, Estate $estate): bool
    {
        return $estate->user_id === $request->user()->id;
    }
}
