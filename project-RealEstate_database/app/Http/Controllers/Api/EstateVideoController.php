<?php
/*
|--------------------------------------------------------------------------
| EstateVideoController — فيديوهات عقار واحد
|--------------------------------------------------------------------------
|
| الغرض:
| - CRUD لجدول estates_videos لمالك العقار فقط
|
| المسارات (تقريباً):
| - GET/POST .../estates/{estate}/videos
| - DELETE .../estates/{estate}/videos/{video}
|
| الارتباطات:
| - Trait: ManagesEstateVideos
| - FormRequest: StoreEstateVideoRequest (التحقق من الملف)
| - Route Model Binding: Estate, EstateVideo
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ManagesEstateVideos;
use App\Http\Requests\StoreEstateVideoRequest;
use App\Models\Estate;
use App\Models\EstateVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstateVideoController extends BaseApiController
{
    use ManagesEstateVideos;

    public function index(Request $request, Estate $estate): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $videos = $estate->videos()->latest()->get()->map(fn (EstateVideo $video) => $this->formatEstateVideo($video));

        return $this->successResponse($videos, 'Estate videos retrieved.');
    }

    public function store(StoreEstateVideoRequest $request, Estate $estate): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $video = $this->createEstateVideo($estate, $request->file('video'));

        return $this->createdResponse(
            $this->formatEstateVideo($video),
            'Video uploaded successfully.',
        );
    }

    public function destroy(Request $request, Estate $estate, EstateVideo $video): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        if ($video->estate_id !== $estate->id) {
            return $this->notFoundResponse('Video does not belong to this estate.');
        }

        $this->deleteEstateVideoFile($video);
        $video->delete();

        return $this->deletedResponse('Video deleted successfully.');
    }

    private function isOwner(Request $request, Estate $estate): bool
    {
        return $estate->user_id === $request->user()->id;
    }
}
