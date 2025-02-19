<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(name="Categories", description="APIs for managing categories")
 */
class CategoryController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     @OA\Response(response=200, description="Categories")
     * )
     */
    public function index()
    {
        $categories = DB::table('categories')->orderBy('name')->get();
        $optionTypes = [];
        // print_r($categories->all());
        foreach($categories->all() as $category) {
            $optionTypes[] = ["value" => $category->id, "text" => $category->name];
        }
        return response()->json($optionTypes);
    }
}
