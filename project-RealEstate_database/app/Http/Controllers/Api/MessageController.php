<?php
/*
|--------------------------------------------------------------------------
| MessageController — الرسائل بين المستخدمين
|--------------------------------------------------------------------------
|
| الغرض:
| - صندوق رسائل (مرسل/مستقبل)، محادثة ثنائية، إرسال، قراءة، حذف
| - عند الإرسال يُنشأ إشعار للمستقبل
|
| الارتباطات:
| - Model messages (جدول messages)
| - StoreMessageRequest
| - Notifications عند store
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\Notifications;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseApiController
{
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Message::query()
            ->where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return $this->successResponse(['count' => $count]);
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $query = Message::query()
            ->with(['sender:id,username,fname,lname', 'receiver:id,username,fname,lname'])
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            });

        if ($request->filled('is_read')) {
            $query->where('is_read', filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('role') && $request->role === 'sent') {
            $query->where('sender_id', $userId);
        }

        if ($request->filled('role') && $request->role === 'inbox') {
            $query->where('receiver_id', $userId);
        }

        $messages = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->successResponse(
            $messages->items(),
            'Messages retrieved.',
            200,
            $this->paginationMeta($messages),
        );
    }

    public function conversation(Request $request, User $user): JsonResponse
    {
        $authId = $request->user()->id;

        $messages = Message::query()
            ->with(['sender:id,username,fname,lname', 'receiver:id,username,fname,lname'])
            ->where(function ($q) use ($authId, $user) {
                $q->where(function ($inner) use ($authId, $user) {
                    $inner->where('sender_id', $authId)->where('receiver_id', $user->id);
                })->orWhere(function ($inner) use ($authId, $user) {
                    $inner->where('sender_id', $user->id)->where('receiver_id', $authId);
                });
            })
            ->oldest()
            ->paginate($request->integer('per_page', 50));

        Message::query()
            ->where('sender_id', $user->id)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->successResponse(
            $messages->items(),
            'Conversation retrieved.',
            200,
            $this->paginationMeta($messages),
        );
    }

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'text' => $request->text,
            'is_read' => false,
        ]);

        $senderName = $request->user()->fname.' '.$request->user()->lname;

        Notifications::create([
            'user_id' => $request->receiver_id,
            'content' => "رسالة جديدة من {$senderName}: ".str($request->text)->limit(120),
            'is_read' => false,
        ]);

        return $this->createdResponse(
            $message->load(['sender:id,username,fname,lname', 'receiver:id,username,fname,lname']),
            'Message sent successfully.',
        );
    }

    public function show(Request $request, Message $message): JsonResponse
    {
        if (! $this->userCanAccess($request->user()->id, $message)) {
            return $this->notFoundResponse('Message not found.');
        }

        if ($message->receiver_id === $request->user()->id && ! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        return $this->successResponse(
            $message->load(['sender:id,username,fname,lname', 'receiver:id,username,fname,lname']),
            'Message retrieved.',
        );
    }

    public function markAsRead(Request $request, Message $message): JsonResponse
    {
        if ($message->receiver_id !== $request->user()->id) {
            return $this->errorResponse('Only the receiver can mark this message as read.', 403);
        }

        $message->update(['is_read' => true]);

        return $this->successResponse(
            $message->fresh(),
            'Message marked as read.',
        );
    }

    public function destroy(Request $request, Message $message): JsonResponse
    {
        if (! $this->userCanAccess($request->user()->id, $message)) {
            return $this->notFoundResponse('Message not found.');
        }

        $message->delete();

        return $this->deletedResponse('Message deleted successfully.');
    }

    private function userCanAccess(int $userId, Message $message): bool
    {
        return $message->sender_id === $userId || $message->receiver_id === $userId;
    }
}
