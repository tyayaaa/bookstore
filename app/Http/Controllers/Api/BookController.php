<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;

class BookController extends Controller
{
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

        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->where('stock', '>', 0);
            } elseif ($request->availability === 'rented') {
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
