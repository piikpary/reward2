<?php

namespace App\Http\Controllers\V2\Announcement;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $announcements = Announcement::query()
            ->with('images')
            ->where('status', 'active')
            ->where(
                'announcement_date',
                '<=',
                now()
            )
            ->orderByDesc('announcement_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (Announcement $announcement) {
                return [
                    'id' => $announcement->id,

                    'title' => $announcement->title,

                    'images' => $announcement->images
                        ->map(function ($image) {
                            return url(
                                Storage::disk('public')
                                    ->url($image->image)
                            );
                        })
                        ->values(),

                    'content' => $announcement->content,

                    'announcement_date' =>
                        $announcement->announcement_date
                            ?->toISOString(),
                ];
            })
            ->values();

        return $this->successResponse(
            $announcements,
            'Announcements retrieved successfully'
        );
    }
}