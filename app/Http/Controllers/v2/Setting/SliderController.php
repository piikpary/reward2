<?php

namespace App\Http\Controllers\v2\Setting;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;

class SliderController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $sliders = Slider::query()
            ->with('images')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->map(function ($slider) {
                $images = $slider->images
                    ->map(function ($image) {
                        return asset('storage/' . $image->image);
                    })
                    ->values();

                if ($images->isEmpty() && !empty($slider->image)) {
                    $images = collect([
                        asset('storage/' . $slider->image),
                    ]);
                }

                return [
                    'slider_id' => $slider->id,
                    'title' => $slider->title,
                    'description' => $slider->description,
                    'link' => $slider->link,
                    'images' => $images,
                ];
            })
            ->values();

        return $this->successResponse($sliders, '');
    }
}