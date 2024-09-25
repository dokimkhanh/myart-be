<?php

namespace App\Http\Controllers;

use App\Models\Art;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ArtController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function feed()
    {
        $userId = auth()->id();

        $followedUserIds = DB::table('follower_user')
            ->where('user_id', $userId)
            ->pluck('follower_id');

        $arts = Art::with(['user', 'comments.user', 'likes', 'user.followers' => function ($query) use ($userId) {
            $query->where('follower_user.user_id', $userId);
        }])
            ->whereIn('user_id', $followedUserIds)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'status' => 200,
            'message' => 'Art retrieved successfully',
            'data' => $arts->items(),
            'pagination' => [
                'total' => $arts->total(),
                'count' => $arts->count(),
                'per_page' => $arts->perPage(),
                'current_page' => $arts->currentPage(),
                'total_pages' => $arts->lastPage(),
                'next_page_url' => $arts->nextPageUrl(),
                'prev_page_url' => $arts->previousPageUrl(),
            ],
        ]);
    }


    public function index(Request $request)
    {
        $query = $request->input('query');

        $artsQuery = Art::with('user', 'comments.user', 'likes')
            ->orderBy('created_at', 'desc');

        if ($query) {
            $artsQuery->where('content', 'like', '%' . $query . '%');
        }

        $arts = $artsQuery->paginate(15);

        return response()->json([
            'status' => 200,
            'message' => 'Art retrieved successfully',
            'data' => $arts->items(),
            'pagination' => [
                'total' => $arts->total(),
                'count' => $arts->count(),
                'per_page' => $arts->perPage(),
                'current_page' => $arts->currentPage(),
                'total_pages' => $arts->lastPage(),
                'next_page_url' => $arts->nextPageUrl(),
                'prev_page_url' => $arts->previousPageUrl(),
            ],
        ]);
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $imagePath = null;

        if ($request->filled('image')) {
            $base64Image = $request->image;

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

        $art = Art::create([
            'content' => $request->content,
            'image' => $imagePath,
            'user_id' => $request->user_id
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Art created successfully',
            'data' => [
                'art' => $art,
                'user_id' => $request->user_id
            ],

        ]);
    }

    public function show($id)
    {
        $art = Art::with('user', 'comments.user', 'likes')->find($id);
        if (!$art) {
            return response()->json(
                [
                    'statusCode' => 404,
                    'message' => 'Art not found'
                ],
                404
            );
        }
        return response()->json([
            'status' => 200,
            'message' => 'Art retrieved successfully',
            'data' => $art
        ]);
    }

    public function update(Request $request, $id)
    {
        $art = Art::find($id);

        if (!$art) {
            return response()->json(['message' => 'Art not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|string',
            'image' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->has('image')) {
            $base64Image = $request->image;
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

        $art->content = $request->content ?? $art->content;
        if ($request->has('image')) {
            $art->image = $imagePath;
        }
        $art->save();

        return response()->json([
            'message' => 'Art updated successfully',
            'data' => $art
        ]);
    }

    public function destroy($id)
    {
        $art = Art::find($id);

        if (auth()->user()->id !== $art->user_id) {
            return response()->json([
                'statusCode' => 403,
                'message' => 'You are not authorized to delete this art'
            ], 403);
        }

        if (!$art) {
            return response()->json([
                'statusCode' => 404,
                'message' => 'Art not found'
            ], 404);
        }

        $art->delete();
        if ($art->image) {
            $imagePath = public_path($art->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        return response()->json([
            'statusCode' => 200,
            'message' => 'Art deleted successfully'
        ], 200);
    }
}
