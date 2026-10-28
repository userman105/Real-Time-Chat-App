<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MessageController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Chat $chat): JsonResponse
    {
        $this->authorize('view', $chat);

        if (!$chat->users()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['error' => 'You are not a member of this chat'], 403);
        }

        $messages = $chat->messages()->with('user:id,name')->latest()->paginate(50);

        return response()->json($messages);
    }

    public function store(Request $request, Chat $chat): JsonResponse
    {
        $this->authorize('sendMessage', $chat);

        // Validate inputs: either text OR file
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'file'    => 'nullable|file|max:25600|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime',
        ]);

        if (!$chat->users()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['error' => 'You are not a member of this chat.'], 403);
        }

        $filePath = null;
        $fileType = 'text';

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // store in /storage/app/public/messages
            $filePath = $file->store('messages', 'public');

            // decide type
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $fileType = 'image';
            } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                $fileType = 'video';
            }
        }

        // Ensure at least content or file exists
        if (!$request->input('content') && !$filePath) {
            return response()->json(['error' => 'Message must have text or a file'], 422);
        }


        $message = $chat->messages()->create([
            'user_id'   => $request->user()->id,
            'content'   => $request->get('content'),
            'file_path' => $filePath,
            'file_type' => $fileType,
        ]);

        return response()->json($message, 201);
    }

    public function markAsRead(Request $request, Message $message): JsonResponse
    {
        $request->user()->messagesRead()->attach($message->id);
        return response()->json(['message' => 'Marked as read']);
    }
    public function destroy(Request $request, Chat $chat, Message $message): JsonResponse
    {
        // Ensure the message belongs to this chat
        if ($message->chat_id !== $chat->id) {
            return response()->json(['error' => 'Message does not belong to this chat'], 400);
        }

        // Only the sender can delete
        if ($message->user_id !== $request->user()->id) {
            return response()->json(['error' => 'You can only delete your own messages'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

}
