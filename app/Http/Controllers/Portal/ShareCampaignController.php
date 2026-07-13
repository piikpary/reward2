<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\ShareCampaign\StoreShareCampaignRequest;
use App\Http\Requests\Portal\ShareCampaign\UpdateShareCampaignRequest;
use App\Models\ShareCampaign;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ShareCampaignController extends Controller
{
    /**
     * Display campaigns.
     */
    public function index(Request $request): View
    {
        $search = trim(
            $request->string('search')->toString()
        );

        $status = $request
            ->string('status')
            ->toString();

        $campaigns = ShareCampaign::query()
            ->withCount([
                'shares',
                'rewards',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($query) use (
                        $search
                    ): void {
                        $query
                            ->where(
                                'title',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'description',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                $status === 'active',
                fn ($query) =>
                    $query->where('is_active', true)
            )
            ->when(
                $status === 'inactive',
                fn ($query) =>
                    $query->where('is_active', false)
            )
            ->when(
                $status === 'published',
                fn ($query) =>
                    $query->where('is_published', true)
            )
            ->when(
                $status === 'draft',
                fn ($query) =>
                    $query->where('is_published', false)
            )
            ->when(
                $status === 'expired',
                fn ($query) =>
                    $query
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<', now())
            )
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'portal.share-campaigns.index',
            compact('campaigns', 'search', 'status')
        );
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view(
            'portal.share-campaigns.create'
        );
    }

    /**
     * Save campaign.
     */
    public function store(
        StoreShareCampaignRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request
                ->file('image')
                ->store(
                    'share-campaigns',
                    'public'
                );
        }

        if (
            empty($data['business_id'])
            && $request->user()
        ) {
            $data['business_id'] =
                $request->user()->business_id ?? null;
        }

        $data['created_by'] =
            $request->user()?->id;

        $data['updated_by'] =
            $request->user()?->id;

        $data['published_at'] =
            $data['is_published']
                ? now()
                : null;

        ShareCampaign::query()->create($data);

        $this->clearApiCampaignCache();

        return redirect()
            ->route(
                'portal.share-campaigns.index'
            )
            ->with(
                'success',
                'Share campaign created successfully.'
            );
    }

    /**
     * Show edit form.
     */
    public function edit(
        ShareCampaign $shareCampaign
    ): View {
        return view(
            'portal.share-campaigns.edit',
            compact('shareCampaign')
        );
    }

    /**
     * Update campaign.
     */
    public function update(
        UpdateShareCampaignRequest $request,
        ShareCampaign $shareCampaign
    ): RedirectResponse {
        $data = $request->validated();

        unset($data['image']);

        $newImagePath = null;
        $oldImagePath = $shareCampaign->image_path;

        if ($request->hasFile('image')) {
            $newImagePath = $request
                ->file('image')
                ->store(
                    'share-campaigns',
                    'public'
                );

            $data['image_path'] = $newImagePath;
        }

        if (
            empty($data['business_id'])
            && $request->user()
        ) {
            $data['business_id'] =
                $request->user()->business_id
                ?? $shareCampaign->business_id;
        }

        $data['updated_by'] =
            $request->user()?->id;

        if ($data['is_published']) {
            $data['published_at'] =
                $shareCampaign->published_at
                ?? now();
        } else {
            $data['published_at'] = null;
        }

        try {
            $shareCampaign->update($data);

            if (
                $newImagePath
                && $oldImagePath
                && $oldImagePath !== $newImagePath
            ) {
                $this->deleteLocalImage(
                    $oldImagePath
                );
            }
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')
                    ->delete($newImagePath);
            }

            throw $exception;
        }

        $this->clearApiCampaignCache();

        return redirect()
            ->route(
                'portal.share-campaigns.index'
            )
            ->with(
                'success',
                'Share campaign updated successfully.'
            );
    }

    /**
     * Activate or deactivate campaign.
     */
    public function toggleActive(
        Request $request,
        ShareCampaign $shareCampaign
    ): RedirectResponse {
        $shareCampaign->update([
            'is_active' =>
                !$shareCampaign->is_active,

            'updated_by' =>
                $request->user()?->id,
        ]);

        $this->clearApiCampaignCache();

        $message = $shareCampaign->is_active
            ? 'Campaign activated successfully.'
            : 'Campaign deactivated successfully.';

        return back()->with(
            'success',
            $message
        );
    }

    /**
     * Publish or unpublish campaign.
     */
    public function togglePublish(
        Request $request,
        ShareCampaign $shareCampaign
    ): RedirectResponse {
        $isPublished =
            !$shareCampaign->is_published;

        $shareCampaign->update([
            'is_published' => $isPublished,

            'published_at' =>
                $isPublished
                    ? now()
                    : null,

            'updated_by' =>
                $request->user()?->id,
        ]);

        $this->clearApiCampaignCache();

        $message = $isPublished
            ? 'Campaign published successfully.'
            : 'Campaign unpublished successfully.';

        return back()->with(
            'success',
            $message
        );
    }

    /**
     * Display customers who shared this campaign.
     */
    public function shares(
        ShareCampaign $shareCampaign
    ): View {
        $shares = $shareCampaign
            ->shares()
            ->with('user')
            ->orderByDesc('shared_at')
            ->paginate(25);

        $statistics = [
            'total_shares' =>
                $shareCampaign
                    ->shares()
                    ->count(),

            'verified_shares' =>
                $shareCampaign
                    ->shares()
                    ->where(
                        'status',
                        'verified'
                    )
                    ->count(),

            'unique_customers' =>
                $shareCampaign
                    ->shares()
                    ->distinct()
                    ->count('user_id'),

            'rewards_awarded' =>
                $shareCampaign
                    ->rewards()
                    ->count(),

            'spins_awarded' =>
                (int) $shareCampaign
                    ->rewards()
                    ->sum('reward_spins'),
        ];

        return view(
            'portal.share-campaigns.shares',
            compact(
                'shareCampaign',
                'shares',
                'statistics'
            )
        );
    }

    /**
     * Soft delete campaign.
     */
    public function destroy(
        ShareCampaign $shareCampaign
    ): RedirectResponse {
        $shareCampaign->update([
            'is_active' => false,
            'is_published' => false,
            'published_at' => null,
        ]);

        $shareCampaign->delete();

        $this->clearApiCampaignCache();

        return redirect()
            ->route(
                'portal.share-campaigns.index'
            )
            ->with(
                'success',
                'Share campaign deleted successfully.'
            );
    }

    private function clearApiCampaignCache(): void
    {
        Cache::forget(
            'reward2:api:share-campaigns:available:v1'
        );
    }

    /**
     * Delete only files stored on the local public disk.
     */
    private function deleteLocalImage(
        ?string $imagePath
    ): void {
        if (!$imagePath) {
            return;
        }

        if (
            Str::startsWith(
                $imagePath,
                ['http://', 'https://']
            )
        ) {
            return;
        }

        Storage::disk('public')
            ->delete($imagePath);
    }
}