<?php

namespace App\Http\Controllers;

use App\Models\Art;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');

        $usersQuery = User::orderBy('created_at', 'desc');

        if ($query) {
            $usersQuery->where('content', 'like', '%' . $query . '%');
        }

        $users = $usersQuery->paginate(15);

        return response()->json([
            'status' => 200,
            'message' => 'Users retrieved successfully',
            'data' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'count' => $users->count(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'total_pages' => $users->lastPage(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'email' => 'required|string|email|max:255|unique:users',
            'bio' => 'nullable|string',
            'is_admin' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'bio' => $request->bio,
            'is_admin' => $request->is_admin ?? false,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function show($id)
    {
        $res = User::with(['comments', 'arts.user', 'arts.likes', 'arts.comments.user', 'followers', 'likes'])->find($id);


        if (!$res) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(
            [
                'status' => 200,
                'message' => 'User retrieved successfully',
                'data' => $res
            ]
        );
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,' . $id,
            'password' => 'sometimes|string|min:6',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'bio' => 'nullable|string',
            'is_admin' => 'nullable|boolean',
            'profile_photo' => 'nullable|image',
        ]);

        if ($request->has('profile_photo')) {
            $base64Image = $request->profile_photo;
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                $imageType = $matches[1];
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                $base64Image = base64_decode($base64Image);

                if ($base64Image === false) {
                    return response()->json(['message' => 'Base64 decode failed'], 400);
                }

                $imageName = time() . '_' . uniqid() . '.' . $imageType;
                $imagePath = 'images/uploads/' . $imageName;

                File::put(public_path($imagePath), $base64Image);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Invalid base64 format'
                ], 400);
            }
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->name = $request->name ?? $user->name;
        $user->username = $request->username ?? $user->username;
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->email = $request->email ?? $user->email;
        $user->bio = $request->bio ?? $user->bio;
        $user->is_admin = $request->is_admin ?? $user->is_admin;
        $user->profile_photo_path = $imagePath ?? $user->profile_photo_path;
        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function profile()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ]);
        }

        $res = User::with(['comments', 'arts.user', 'arts.likes', 'arts.comments.user', 'followers', 'likes'])->find($user);

        return response()->json([
            'status' => 200,
            'message' => 'User retrieved successfully',
            'data' => [
                'user' => $res,
            ]
        ]);
    }

    public function topFollowedUsers()
    {
        $topUsers = User::withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Top followed users retrieved successfully',
            'data' => $topUsers
        ]);
    }
}
