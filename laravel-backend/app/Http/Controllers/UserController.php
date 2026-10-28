<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): Collection
    {
        $query = User::query();

        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('email', 'like', '%' . $request->q . '%');
        }

        return $query->get();
    }
    public function updateProfilePicture(Request $request): JsonResponse
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $user = $request->user();

        // Store the file in storage/app/public/profile_pictures
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Save path to DB
        $user->profile_picture = $path;
        $user->save();

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'profile_picture_url' => asset('storage/' . $path),
        ]);
    }
    public function blockUser(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $userToBlock = User::where('email', $request->email)->first();

        if ($request->user()->id === $userToBlock->id) {
            return response()->json(['error' => 'You cannot block yourself'], 400);
        }

        if ($request->user()->hasBlocked($userToBlock)) {
            return response()->json(['message' => 'User already blocked']);
        }


        $request->user()->blockedUsers()->attach($userToBlock->id);


        $sharedChats = $request->user()->chats()
            ->where('type', 'private') // only private
            ->whereHas('users', function ($q) use ($userToBlock) {
                $q->where('users.id', $userToBlock->id);
            })
            ->get();

        foreach ($sharedChats as $chat) {
            $chat->users()->detach($request->user()->id);


            if ($chat->users()->count() < 2) {
                $chat->delete();
            }
        }

        return response()->json([
            'message' => 'User blocked and left shared private chats',
            'left_chats' => $sharedChats->pluck('id'),
        ]);
    }



// Unblock
    public function unblockUser(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $userToUnblock = User::where('email', $request->email)->first();

        $request->user()->blockedUsers()->detach($userToUnblock->id);

        return response()->json(['message' => 'User unblocked successfully']);
    }


    public function show(User $user): User
    {
        return $user;
    }
}
