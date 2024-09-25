<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->id === auth()->user()->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        if (auth()->user()->isFollowing($user)) {
            return response()->json(['message' => 'You are already following this user'], 400);
        }

        $result = auth()->user()->following()->attach($user);

        if (!$result) {
            return response()->json(['message' => 'Failed to follow user'], 500);
        }
        return response()->json(['message' => 'User followed successfully'], 200);
    }

    public function unfollow($id)
    {

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->id === auth()->user()->id) {
            return response()->json(['message' => 'You cannot unfollow yourself'], 400);
        }

        if (!auth()->user()->isFollowing($user)) {
            return response()->json(['message' => 'You are not following this user'], 400);
        }


        $unfollow = auth()->user()->following()->detach($user);

        if (!$unfollow) {
            return response()->json(['message' => 'Failed to unfollow user'], 500);
        }

        return response()->json(['message' => 'User unfollowed successfully'], 200);
    }

    public function checkFollow($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['isFollowing' => auth()->user()->isFollowing($user)], 200);
    }
}
