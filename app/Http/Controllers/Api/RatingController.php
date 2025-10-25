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

class RatingController extends Controller
{
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
