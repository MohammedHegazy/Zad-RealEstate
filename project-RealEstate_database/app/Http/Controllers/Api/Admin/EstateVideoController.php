<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\ManagesEstateVideos;
use App\Http\Requests\StoreEstateVideoRequest;
use App\Models\Estate;
use App\Models\EstateVideo;
use Illuminate\Http\JsonResponse;

class EstateVideoController extends BaseApiController
{
    use ManagesEstateVideos;

    public function index(Estate $estate): JsonResponse
    {
        $videos = $estate->videos()->latest()->get()->map(fn (EstateVideo $video) => $this->formatEstateVideo($video));

        return $this->successResponse($videos, 'Estate videos retrieved.');
    }

    public function store(StoreEstateVideoRequest $request, Estate $estate): JsonResponse
    {
        $video = $this->createEstateVideo($estate, $request->file('video'));

        return $this->createdResponse(
            $this->formatEstateVideo($video),
            'Video uploaded successfully.',
        );
    }

    public function destroy(Estate $estate, EstateVideo $video): JsonResponse
    {
        if ($video->estate_id !== $estate->id) {
            return $this->notFoundResponse('Video does not belong to this estate.');
        }

        $this->deleteEstateVideoFile($video);
        $video->delete();

        return $this->deletedResponse('Video deleted successfully.');
    }
}
