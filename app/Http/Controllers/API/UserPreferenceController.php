<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="User Preferences", description="APIs for managing user preferences")
 */
class UserPreferenceController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/user/preferences",
     *     summary="Save user preferences",
     *     tags={"User Preferences"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sources", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="authors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Preferences saved"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'sources' => 'nullable|array',
            'categories' => 'nullable|array',
            'authors' => 'nullable|array',
        ]);

        $preference = UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return response(['message' => 'Preferences saved', 'preferences' => $preference]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/preferences",
     *     summary="Get user preferences",
     *     tags={"User Preferences"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User preferences")
     * )
     */
    public function show()
    {
        $preferences = Auth::user()->preference;
        return response($preferences);
    }
}
