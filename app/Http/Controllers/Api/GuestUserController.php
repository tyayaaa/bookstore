<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Guest-user",
 *     description="Endpoints for guest user registration"
 * )
 */
class GuestUserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auto-register",
     *     summary="Register a new guest user",
     *     description="Creates a temporary guest account with a random name and email. This can be used by users who don't want to register manually.",
     *     tags={"Guest"},
     *     @OA\Response(
     *         response=200,
     *         description="Guest user registered successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user_id", type="integer", example=45)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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
