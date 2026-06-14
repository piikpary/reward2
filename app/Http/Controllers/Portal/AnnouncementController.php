<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $announcements = Announcement::query()
            ->with([
                'images',
                'creator',
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where(
                            'title',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'content',
                            'like',
                            "%{$search}%"
                        );
                });
            })
            ->when(
                $status !== null && $status !== '',
                function ($query) use ($status) {
                    $query->where('status', $status);
                }
            )
            ->orderByDesc('announcement_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totalAnnouncements = Announcement::count();

        $activeAnnouncements = Announcement::query()
            ->where('status', 'active')
            ->count();

        $publishedAnnouncements = Announcement::query()
            ->where('status', 'active')
            ->where(
                'announcement_date',
                '<=',
                now()
            )
            ->count();

        return view(
            'portal.announcements.index',
            compact(
                'announcements',
                'search',
                'status',
                'totalAnnouncements',
                'activeAnnouncements',
                'publishedAnnouncements'
            )
        );
    }

    public function create()
    {
        return view('portal.announcements.create', [
            'announcement' => new Announcement(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'content' => [
                'required',
                'string',
            ],

            'announcement_date' => [
                'required',
                'date',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

            'images' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $validated
        ) {
            $announcement = Announcement::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'announcement_date' =>
                    $validated['announcement_date'],
                'status' => $validated['status'],
                'created_by' => auth()->id(),
            ]);

            foreach (
                $request->file('images', [])
                as $index => $image
            ) {
                $path = $image->store(
                    'announcements',
                    'public'
                );

                $announcement->images()->create([
                    'image' => $path,
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()
            ->route('portal.announcements.index')
            ->with(
                'success',
                'Announcement created successfully.'
            );
    }

    public function edit(Announcement $announcement)
    {
        $announcement->load('images');

        return view('portal.announcements.edit', [
            'announcement' => $announcement,
        ]);
    }

    public function update(
        Request $request,
        Announcement $announcement
    ) {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'content' => [
                'required',
                'string',
            ],

            'announcement_date' => [
                'required',
                'date',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

            'images' => [
                'nullable',
                'array',
                'max:10',
            ],

            'images.*' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $validated,
            $announcement
        ) {
            $announcement->update([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'announcement_date' =>
                    $validated['announcement_date'],
                'status' => $validated['status'],
            ]);

            $currentMaximumOrder = (int) $announcement
                ->images()
                ->max('sort_order');

            foreach (
                $request->file('images', [])
                as $index => $image
            ) {
                $path = $image->store(
                    'announcements',
                    'public'
                );

                $announcement->images()->create([
                    'image' => $path,
                    'sort_order' =>
                        $currentMaximumOrder + $index + 1,
                ]);
            }
        });

        return redirect()
            ->route('portal.announcements.index')
            ->with(
                'success',
                'Announcement updated successfully.'
            );
    }

    public function destroy(Announcement $announcement)
    {
        DB::transaction(function () use ($announcement) {
            $announcement->load('images');

            foreach ($announcement->images as $image) {
                if (
                    $image->image
                    && Storage::disk('public')
                        ->exists($image->image)
                ) {
                    Storage::disk('public')
                        ->delete($image->image);
                }
            }

            $announcement->delete();
        });

        return redirect()
            ->route('portal.announcements.index')
            ->with(
                'success',
                'Announcement deleted successfully.'
            );
    }

    public function toggleStatus(
        Announcement $announcement
    ) {
        $announcement->update([
            'status' =>
                $announcement->status === 'active'
                    ? 'inactive'
                    : 'active',
        ]);

        return back()->with(
            'success',
            'Announcement status updated successfully.'
        );
    }

    public function deleteImage(
        Announcement $announcement,
        AnnouncementImage $image
    ) {
        abort_unless(
            (int) $image->announcement_id
            === (int) $announcement->id,
            404
        );

        /*
         * Prevent removing the final image.
         */
        if ($announcement->images()->count() <= 1) {
            return back()->withErrors([
                'image' =>
                    'An announcement must have at least one image.',
            ]);
        }

        DB::transaction(function () use ($image) {
            if (
                $image->image
                && Storage::disk('public')
                    ->exists($image->image)
            ) {
                Storage::disk('public')
                    ->delete($image->image);
            }

            $image->delete();
        });

        return back()->with(
            'success',
            'Announcement image deleted successfully.'
        );
    }
}