<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['data' => $request->user()->notifications]);
    }

    public function markAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['data' => [
            'success' => true,
        ]]);
    }
}
