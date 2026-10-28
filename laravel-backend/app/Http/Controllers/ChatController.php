<?php
namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChatController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $chats = $request->user()
            ->chats()
            ->with('users:id,name,email')
            ->get();


        return response()->json($chats);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:private,group',
        ]);

        $chat = Chat::create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        // attach creator as admin
        $chat->users()->attach($request->user()->id, ['role' => 'admin']);

        return response()->json($chat, 201);
    }

    public function show(Chat $chat): Chat
    {
        return $chat->load('users');
    }

    public function join(Request $request, Chat $chat): JsonResponse
    {
        $this->authorize('join', $chat);

        $request->validate([
            'admin_email' => 'required|email|exists:users,email',
        ]);


        $admin = $chat->users()
            ->wherePivot('role', 'admin')
            ->where('email', $request->admin_email)
            ->first();

        if (!$admin) {
            return response()->json(['error' => 'Invalid admin email for this chat'], 403);
        }


        if ($chat->users()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['error' => 'Already a member'], 400);
        }


        $chat->users()->attach($request->user()->id, ['role' => 'member']);

        return response()->json(['message' => 'Joined chat']);
    }


    public function addUserByEmail(Request $request, Chat $chat): JsonResponse
    {

        $this->authorize('addUser', $chat);

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($chat->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'User already in chat'], 400);
        }

        if ($user->hasBlocked($request->user()) || $request->user()->hasBlocked($user)) {
            return response()->json(['error' => 'You cannot add this user to a chat due to blocking'], 403);
        }


        $chat->users()->attach($user->id, ['role' => 'member']);

        return response()->json([
            'message' => 'User added successfully',
            'user' => $user->only(['id', 'name', 'email'])
        ]);
    }


    public function leave(Request $request, Chat $chat): JsonResponse
    {
        $chat->users()->detach($request->user()->id);

        return response()->json(['message' => 'Left chat']);
    }

}
