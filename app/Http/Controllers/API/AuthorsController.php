<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(name="Authors", description="APIs for managing authors")
 */
class AuthorsController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/authors",
     *     summary="Get all authors",
     *     tags={"Authors"},
     *     @OA\Response(response=200, description="Authors")
     * )
     */
    public function index()
    {
        $authors = DB::table('authors')->orderBy('name')->get();
        $optionTypes = [];
        foreach($authors->all()  as $author) {
            $optionTypes[] = ["value" => $author->id, "text" => $author->name];
        }
        return response()->json($optionTypes);
    }
}
