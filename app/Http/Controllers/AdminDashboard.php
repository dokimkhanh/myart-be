<?php

namespace App\Http\Controllers;

use App\Models\Art;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AdminDashboard extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 200,
            'message' => 'Dashboard retrieved successfully',
            'data' => [
                'art_count' => Art::count(),
                'user_count' => User::count(),
                'comment_count' => Comment::count(),
            ]
        ]);
    }
}
