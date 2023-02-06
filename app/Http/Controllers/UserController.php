<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{

    public function me(Request $request)
    {
        if ($request->user()) {
            return new UserResource($request->user());
        } else {
            return response()->json(['message' => 'Unauthenticated']);
        }
    }

    public function follow(User $user, Request $request)
    {
        $currentUser = $request->user();
        $currentUser->following()->toggle($user);

        // Save this against the user so it's easy to query.
        $user->number_followers = $user->followers()->count();
        $user->save();

        return response()->json([
            'data' => [
                'success' => true,
            ]
        ]);
    }
}
