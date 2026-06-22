<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $messages = Message::query()->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $messages->items(),
            'Messages retrieved.',
            200,
            $this->paginationMeta($messages),
        );
    }

    public function conversation(Request $request): JsonResponse
    {
        return $this->errorResponse('Not implemented.', 501);
    }

    public function show(Message $message): JsonResponse
    {
        return $this->successResponse($message, 'Message retrieved.');
    }

    public function destroy(Message $message): JsonResponse
    {
        $message->delete();

        return $this->deletedResponse('Message deleted.');
    }
}
