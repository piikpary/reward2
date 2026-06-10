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
            ->where('status', true)
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->map(function ($slider) {
                return [
                    'slider_id' => $slider->id,
                    'image_url' => $slider->image ? asset('storage/' . $slider->image) : null,
                ];
            })
            ->values();

        return $this->successResponse($sliders, '');
    }
}