<?php

namespace App\Http\Controllers;

use App\Models\Toot;
use Illuminate\Http\Request;
use App\Http\Resources\TootResource;
use App\Notifications\TootLikeNotification;
use App\Notifications\TootReplyNotification;

class TootController extends Controller
{
    public function index()
    {
        return TootResource::collection(
            Toot::all()
        );
    }

    public function store(Request $request)
    {
        $toot = $request->user()->toots()->create($request->all());

        if ($request->has('reply_id')) {
            $parent = Toot::find($request->reply_id);
            $toot->parent()->associate($parent);
            $toot->save();

            $parent->user()->first()->notify(new TootReplyNotification($toot));
        }

        return new TootResource($toot);
    }

    public function like(Toot $toot, Request $request)
    {
        if ($toot->isLikedBy($request->user())) {
            $toot->likes()->where('user_id', $request->user()->id)->delete();
            $toot->number_likes--;
        } else {
            $toot->likes()->create([
                'user_id' => $request->user()->id,
            ]);
            $toot->number_likes++;
            $toot->user()->first()->notify(new TootLikeNotification($toot));
        }

        $toot->save();

        return response()->json(['data' => [
            'success' => true,
        ]]);
    }
}
