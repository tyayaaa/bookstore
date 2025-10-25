<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\Rating;
use App\Models\Book;
use App\Models\Author;

/**
 * @OA\Tag(
 *     name="Rating",
 *     description="Endpoints related to rating"
 * )
 */
class RatingController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/rating",
     *     summary="Submit a rating for a book",
     *     description="Allows a user to rate a book (1–10) and leave an optional comment. A user can only rate a book once, and only one rating per 24 hours is allowed.",
     *     tags={"Rating"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"book_id", "author_id", "user_id", "rating"},
     *             @OA\Property(property="book_id", type="integer", description="The ID of the book to rate"),
     *             @OA\Property(property="author_id", type="integer", description="The ID of the author of the book"),
     *             @OA\Property(property="user_id", type="integer", description="The ID of the user giving the rating"),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=10, description="The rating value (1–10)"),
     *             @OA\Property(property="comment", type="string", nullable=true, description="Optional comment for the rating")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating saved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rating saved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=123),
     *                 @OA\Property(property="book_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=13),
     *                 @OA\Property(property="rating", type="integer", example=8),
     *                 @OA\Property(property="comment", type="string", example="Bagus banget!"),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-25T09:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-25T09:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation or logical error (e.g. duplicate rating, too soon to rate again)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You have given a rating today. You can add rating again after 23 hours."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="rating",
     *                     type="array",
     *                     @OA\Items(type="string", example="You have given a rating today. You can add rating again after 23 hours.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while saving the rating.")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'author_id' => 'required|exists:authors,id',
            'rating' => 'required|integer|min:1|max:10',
            'comment' => 'nullable|string|max:255',
            'user_id' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $book = Book::find($validated['book_id']);

            if ($book->author_id != $validated['author_id']) {
                throw ValidationException::withMessages([
                    'book_id' => ['This book is not owned by the selected author.'],
                ]);
            }

            $existing = Rating::where('book_id', $validated['book_id'])
                ->where('user_id', $validated['user_id'])
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'rating' => ['You have already rated this book.'],
                ]);
            }

            $lastRating = Rating::where('user_id', $validated['user_id'])
                ->orderByDesc('created_at')
                ->first();

            if ($lastRating && $lastRating->created_at->gt(now()->subDay())) {
                $remaining = $lastRating->created_at->addDay()->diffForHumans(null, true);
                throw ValidationException::withMessages([
                    'rating' => ["You have given a rating today. You can add rating again after $remaining."],
                ]);
            }

            $rating = Rating::create([
                'book_id' => $validated['book_id'],
                'user_id' => $validated['user_id'],
                'rating'  => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rating saved successfully.',
                'data' => $rating,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the rating.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
