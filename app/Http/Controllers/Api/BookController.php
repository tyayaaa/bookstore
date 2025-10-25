<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;

/**
 * @OA\Info(
 *     title="Bookstore API",
 *     version="1.0.0",
 *     description="API documentation for Bookstore project"
 * ),
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Local server"
 * )
 */

class BookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/book",
     *     summary="Get list of books (with optional filters)",
     *     tags={"Books"},
     *     description="Retrieve books with optional filters like author, category, year, rating, and more. You can try any combination of parameters.",
     * 
     *     @OA\Parameter(
     *         name="author_id",
     *         in="query",
     *         description="Filter by author ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="categories",
     *         in="query",
     *         description="Filter by one or more category IDs (comma-separated)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="year_start",
     *         in="query",
     *         description="Filter by publication start year",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="year_end",
     *         in="query",
     *         description="Filter by publication end year",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Book availability status (available or rented)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"available", "rented"})
     *     ),
     *     @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Filter by location ID (if applicable)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="rating_min",
     *         in="query",
     *         description="Minimum rating filter",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="rating_max",
     *         in="query",
     *         description="Maximum rating filter",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort by rating, votes, alpha, or weighted",
     *         required=false,
     *         @OA\Schema(type="string", enum={"rating","votes","alpha","weighted"})
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Results per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="List of books retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid query parameter"
     *     )
     * )
     */


    public function index(Request $request)
    {
        $query = Book::query()
            ->with(['author', 'category'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->withAvg(['ratings as avg_rating_last_7_days' => function ($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            }], 'rating');

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->filled('location_id')) {
            $query->where('store_id', $request->location_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%")
                    ->orWhereHas('author', fn($a) => $a->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        if ($request->filled('status') || $request->filled('availability')) {
            $status = $request->get('status', $request->get('availability'));

            if ($status === 'available') {
                $query->where('stock', '>', 0);
            } elseif ($status === 'rented') {
                $query->where('stock', '=', 0);
            }
        }


        if ($request->has(['rating_min', 'rating_max'])) {
            $ratingMin = $request->rating_min;
            $ratingMax = $request->rating_max;
            $query->havingRaw('ratings_avg_rating BETWEEN ? AND ?', [$ratingMin, $ratingMax]);
        }

        $sortBy = $request->get('sort', 'weighted');
        $sortOrder = $request->get('order', 'desc');

        switch ($sortBy) {
            case 'votes':
                $query->orderBy('ratings_count', $sortOrder);
                break;

            case 'recent':
                $query->orderBy('avg_rating_last_7_days', $sortOrder);
                break;

            case 'alpha':
                $query->orderBy('title', $sortOrder);
                break;

            case 'rating':
                $query->orderBy('ratings_avg_rating', $sortOrder);
                break;

            default:
                $query->orderByRaw('COALESCE(ratings_avg_rating, 0) * LOG10(ratings_count + 1) DESC');
                break;
        }

        $perPage = $request->get('per_page', 10);
        $books = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'List Data Buku',
            'data' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'total' => $books->total(),
                'books' => BookResource::collection($books->items()),
            ],
        ]);
    }
}
