<?php

namespace App\Http\Controllers\Api\Customer_Loyality_Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(){
        $banners = Banner::where('status',1)
                    ->orderBy('display_order')
                    ->get()
                    ->map(fn($banner) =>[
                        'title' => $banner->title,
                        'image_url' => asset($banner->image),
                        'display_order' => $banner->display_order
                    ]);

        return response()->json([
             'banners' => $banners
        ]);
    }
}
