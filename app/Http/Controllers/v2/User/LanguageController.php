<?php

namespace App\Http\Controllers\v2\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    use ApiResponse;

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => [
                'required',
                'string',
                'in:en,km',
            ],
        ]);

        $user = $request->user();

        $user->update([
            'language' => $validated['language'],
        ]);

        return $this->successResponse([
            'userId' => (string) $user->id,
            'language' => $user->language,
        ], 'Language updated successfully');
    }
}