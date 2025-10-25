<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Author;

/**
 * @OA\Tag(
 *     name="Authors",
 *     description="Endpoints related to authors and their rankings"
 * )
 */
class AuthorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/authors/top",
     *     summary="Get top authors",
     *     description="Retrieve a ranked list of top authors based on popularity, average rating, or trending score.",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort mode: popularity, average, or trending",
     *         required=false,
     *         @OA\Schema(type="string", enum={"popularity","average","trending"})
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top authors to return (default: 10)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Top authors retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid query parameters"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $tab = $request->query('sort', 'popularity');
        $limit = $request->query('limit', 10);

        $now = Carbon::now();
        $recentStart = $now->copy()->subDays(30);
        $previousStart = $now->copy()->subDays(60);

        $base = DB::table('ratings')
            ->join('books', 'ratings.book_id', '=', 'books.id')
            ->join('authors', 'books.author_id', '=', 'authors.id')
            ->select(
                'authors.id as author_id',
                'authors.name as author_name',
                DB::raw('COUNT(ratings.id) as total_ratings'),
                DB::raw('AVG(ratings.rating) as avg_rating')
            )
            ->groupBy('authors.id', 'authors.name');

        switch ($tab) {
            case 'average':
                $query = $base->orderByDesc('avg_rating');
                break;

            case 'trending':
                $recent = DB::table('ratings')
                    ->join('books', 'ratings.book_id', '=', 'books.id')
                    ->join('authors', 'books.author_id', '=', 'authors.id')
                    ->whereBetween('ratings.created_at', [$recentStart, $now])
                    ->groupBy('authors.id')
                    ->select(
                        'authors.id',
                        DB::raw('AVG(ratings.rating) as recent_avg'),
                        DB::raw('COUNT(ratings.id) as recent_votes')
                    );

                $previous = DB::table('ratings')
                    ->join('books', 'ratings.book_id', '=', 'books.id')
                    ->join('authors', 'books.author_id', '=', 'authors.id')
                    ->whereBetween('ratings.created_at', [$previousStart, $recentStart])
                    ->groupBy('authors.id')
                    ->select(
                        'authors.id',
                        DB::raw('AVG(ratings.rating) as prev_avg')
                    );

                $query = DB::table('authors')
                    ->leftJoinSub($recent, 'recent', 'recent.id', '=', 'authors.id')
                    ->leftJoinSub($previous, 'previous', 'previous.id', '=', 'authors.id')
                    ->select(
                        'authors.id',
                        'authors.name',
                        DB::raw('COALESCE(recent.recent_avg - previous.prev_avg, 0) * COALESCE(recent.recent_votes, 1) as trending_score'),
                        DB::raw('COALESCE(recent.recent_avg, 0) as recent_avg'),
                        DB::raw('COALESCE(previous.prev_avg, 0) as prev_avg'),
                        DB::raw('COALESCE(recent.recent_votes, 0) as recent_votes')
                    )
                    ->orderByDesc('trending_score');
                break;

            case 'popularity':
            default:
                $query = DB::table('ratings')
                    ->join('books', 'ratings.book_id', '=', 'books.id')
                    ->join('authors', 'books.author_id', '=', 'authors.id')
                    ->where('ratings.rating', '>', 5)
                    ->select(
                        'authors.id as author_id',
                        'authors.name as author_name',
                        DB::raw('COUNT(ratings.id) as voter_count'),
                        DB::raw('AVG(ratings.rating) as avg_rating')
                    )
                    ->groupBy('authors.id', 'authors.name')
                    ->orderByDesc('voter_count');
                break;
        }

        $authors = $query->limit($limit)->get();

        foreach ($authors as $author) {
            $bestBook = DB::table('books')
                ->join('ratings', 'ratings.book_id', '=', 'books.id')
                ->where('books.author_id', $author->author_id ?? $author->id)
                ->select('books.title', DB::raw('AVG(ratings.rating) as avg'))
                ->groupBy('books.id')
                ->orderByDesc('avg')
                ->first();

            $worstBook = DB::table('books')
                ->join('ratings', 'ratings.book_id', '=', 'books.id')
                ->where('books.author_id', $author->author_id ?? $author->id)
                ->select('books.title', DB::raw('AVG(ratings.rating) as avg'))
                ->groupBy('books.id')
                ->orderBy('avg')
                ->first();

            $author->best_book = $bestBook?->title;
            $author->worst_book = $worstBook?->title;
        }

        return response()->json([
            'sort_mode' => $tab,
            'data' => $authors,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/authors",
     *     summary="Get all authors",
     *     description="Retrieve the full list of all authors in the system.",
     *     tags={"Authors"},
     *     @OA\Response(
     *         response=200,
     *         description="List of authors retrieved successfully"
     *     )
     * )
     */

    public function all()
    {
        $authors = Author::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $authors
        ]);
    }
}
