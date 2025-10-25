<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class GuestUserController extends Controller
{
    public function register(Request $request)
    {
        $user = User::create([
            'name' => 'Guest_' . Str::random(5),
            'email' => Str::random(10) . '@guest.local',
            'password' => bcrypt(Str::random(10)),
        ]);

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
        ]);
    }
}
