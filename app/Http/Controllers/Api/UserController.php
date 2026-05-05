<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email')
            ->orderBy('id')
            ->get();

        return response()->json([
            'message' => 'Users retrieved successfully.',
            'data' => $users,
        ]);
    }
}