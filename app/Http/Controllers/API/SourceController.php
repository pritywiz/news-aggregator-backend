<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(name="Source", description="APIs for managing source")
 */
class SourceController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/source",
     *     summary="Get all source",
     *     tags={"Source"},
     *     @OA\Response(response=200, description="Source")
     * )
     */
    public function index()
    {
        $sources = DB::table('sources')->orderBy('name')->get();
        $optionTypes = [];
        foreach($sources->all() as $source) {
            $optionTypes[] = ["value" => $source->id, "text" => $source->name];
        }
        return response()->json($optionTypes);
    }
}
