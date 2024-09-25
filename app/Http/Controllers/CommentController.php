<?php

namespace App\Http\Controllers;

use App\Models\Art;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'art_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $art = Art::find($request['art_id']);
        if (!$art) {
            return response()->json([
                'status' => 404,
                'message' => 'Art not found'
            ]);
        }

        $cmt = Comment::create([
            'art_id' => $request['art_id'],
            'content' => $request['comment'],
            'user_id' => auth()->user()->id,
        ]);

        if (!$cmt) {
            return response()->json(['message' => 'Cannot create comment'], 500);
        }

        return response()->json([
            'status' => 201,
            'data' => $cmt,
            'message' => 'Comment created successfully'
        ], 201);
    }

    public function getAll(Request $request)
    {
        $query = $request->input('query');

        $cmtsQuery = Comment::with('art', 'user')->orderBy('created_at', 'desc');

        if ($query) {
            $cmtsQuery->where('content', 'like', '%' . $query . '%');
        }

        $cmts = $cmtsQuery->paginate(15);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Comments retrieved successfully',
            'data' => $cmts->items(),
            'pagination' => [
                'total' => $cmts->total(),
                'count' => $cmts->count(),
                'per_page' => $cmts->perPage(),
                'current_page' => $cmts->currentPage(),
                'total_pages' => $cmts->lastPage(),
                'next_page_url' => $cmts->nextPageUrl(),
                'prev_page_url' => $cmts->previousPageUrl(),
            ],
        ]);
    }

    public function destroy($comment_id)
    {
        $cmt = Comment::find($comment_id);

        if (!$cmt) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if (auth()->user()->id !== $cmt->user_id || auth()->user()['is_admin'] !== 1) {
            return response()->json([
                'statusCode' => 403,
                'message' => 'You are not authorized to delete this comment'
            ], 403);
        }

        $respone = $cmt->delete();
        if (!$respone) {
            return response()->json([
                'statusCode' => 500,
                'message' => 'Cannot delete comment'
            ], 500);
        }

        return response()->json([
            'statusCode' => 200,
            'message' => 'Comment deleted successfully'
        ], 200);
    }
}
