<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $sliders = Slider::query()
            ->with('images')
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('link', 'like', "%{$search}%");
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('sort_order')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('portal.sliders.index', compact('sliders', 'search', 'status'));
    }

    public function create()
    {
        return view('portal.sliders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'url', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $slider = Slider::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'link' => $validated['link'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'status' => $validated['status'],
            ]);

            foreach ($request->file('images', []) as $index => $image) {
                $path = $image->store('sliders', 'public');

                $slider->images()->create([
                    'image' => $path,
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()
            ->route('portal.sliders.index')
            ->with('success', 'Slider created successfully.');
    }

    public function edit(Slider $slider)
    {
        $slider->load('images');

        return view('portal.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'url', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer', 'exists:slider_images,id'],
        ]);

        DB::transaction(function () use ($request, $slider, $validated) {
            $slider->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'link' => $validated['link'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'status' => $validated['status'],
            ]);

            if (!empty($validated['delete_image_ids'])) {
                $imagesToDelete = $slider->images()
                    ->whereIn('id', $validated['delete_image_ids'])
                    ->get();

                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->image);
                    $image->delete();
                }
            }

            $currentCount = $slider->images()->count();

            foreach ($request->file('images', []) as $index => $image) {
                $path = $image->store('sliders', 'public');

                $slider->images()->create([
                    'image' => $path,
                    'sort_order' => $currentCount + $index + 1,
                ]);
            }
        });

        return redirect()
            ->route('portal.sliders.index')
            ->with('success', 'Slider updated successfully.');
    }

    public function destroy(Slider $slider)
    {
        foreach ($slider->images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        if (!empty($slider->image)) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();

        return redirect()
            ->route('portal.sliders.index')
            ->with('success', 'Slider deleted successfully.');
    }
}