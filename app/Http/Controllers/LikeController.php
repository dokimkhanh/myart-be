<?php

namespace App\Http\Controllers;

use App\Models\Art;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    public function like($id)
    {
        $art = Art::find($id);

        if (!$art) {
            return response()->json(['message' => 'Art not found'], 404);
        }

        if (auth()->user()->isLikes($art)) {
            return response()->json(['message' => 'You have already liked'], 400);
        }
        // dd($art);

        try {
            auth()->user()->likes()->attach($art);
            return response()->json(['message' => 'Like successfully'], 500);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Failed to like'], 200);
        }
    }


    public function unlike($id)
    {
        $art = Art::find($id);
        if (!$art) {
            return response()->json(['message' => 'Art not found'], 404);
        }

        if (!auth()->user()->isLikes($art)) {
            return response()->json(['message' => 'You have not liked'], 400);
        }

        try {
            auth()->user()->likes()->detach($art);
            return response()->json(['message' => 'Unlike successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Failed to unlike'], 500);
        }
    }


    public function isLike($id)
    {
        $art = Art::find($id);

        if (!$art) {
            return response()->json(['status' => 404, 'message' => 'Art not found'], 404);
        }

        if (!auth()->user()->$this->isLikes($art)) {
            return response()->json(
                [
                    'status' => 200,
                    'is_liked' => false

                ],
                200
            );
        }

        return response()->json(
            [
                'status' => 200,
                'is_liked' => true
            ],
            200
        );
    }
}
