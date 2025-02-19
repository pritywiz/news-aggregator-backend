<?php

namespace App\Http\Controllers\API;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(name="Articles", description="APIs for fetching news articles")
 */
class ArticleController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Get a list of articles",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Search for articles by keyword",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter by news source",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="author",
     *         in="query",
     *         description="Filter by news author",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by publication date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Article"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        
        $query = Article::query();
        $query->select('articles.*', 'sources.name as source', 'authors.name as author', 'categories.name as category');
        $query->join('sources', 'sources.id', '=', 'articles.source_id');
        $query->join('authors', 'authors.id', '=', 'articles.author_id');
        $query->join('categories', 'categories.id', '=', 'articles.category_id');
        if ($request->has('user_id') && $request->user_id) {
            $preferences = User::find($request->user_id)->preference;
            if($preferences->categories)
                $query->whereIn('category_id', $preferences->categories);
            if($preferences->sources)
                $query->whereIn('source_id', $preferences->sources);
            if($preferences->authors)
                $query->whereIn('author_id', $preferences->authors);
        }

        // Search by keyword
        if ($request->has('keyword') && $request->keyword) {
            $query->whereAny(['title', 'content'], 'like', '%' . $request->keyword . '%');
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by source
        if ($request->has('source') && $request->source) {
            $query->where('source_id', $request->source);
        }

        // Filter by source
        if ($request->has('author') && $request->author) {
            $query->where('author_id', $request->author);
        }

        // Filter by date
        if ($request->has('dateFrom') && $request->dateFrom) {
            $query->whereDate('published_at', '>=', $request->dateFrom);
        }
        if ($request->has('dateTo') && $request->dateTo) {
            $query->whereDate('published_at', '<=', $request->dateTo);
        }

        // Pagination
        // $perPage = $request->query('per_page', 10); // Default 10 per page
        $limit = env("LIMIT");
        
        $articles = $query->paginate($limit);

        return response()->json($articles);
        // return response()->json($query->ddRawSql());
    }
}
