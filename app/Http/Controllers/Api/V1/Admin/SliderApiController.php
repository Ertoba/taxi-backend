<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Models\Slider;

class SliderApiController extends Controller
{
    use MediaUploadingTrait,ResponseTrait;

    public function sliders()
    {

        $data = Slider::where('status', '1')->get()->map(function ($slider) {
            return [
                'id' => $slider->id,
                'heading' => $slider->heading,
                'url' => $slider->url,
                'image' => $slider->image[0]->getUrl(), // Adjust based on your actual relationship
            ];
        })->toArray();

        return response()->json([
            'status' => 200,
            'message' => trans('global.slider_data'),
            'data' => $data,
        ]);

    }
}
