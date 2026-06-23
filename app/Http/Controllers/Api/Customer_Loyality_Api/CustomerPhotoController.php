<?php

namespace App\Http\Controllers\Api\Customer_Loyality_Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CustomerPhotoUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomerPhotoController extends Controller
{

    public function show(Request $request)
    {
        $photo = CustomerPhotoUpload::where('user_id', $request->user()->id)->latest()->first();

        if (!$photo) {
            return response()->json(['has_photo' => false, 'photo' => null]);
        }

        return response()->json([
            'has_photo' => true,
            'photo'     => [
                'photo_url'        => asset($photo->photo_path),
                'consent_given'    => $photo->consent_given,
                'consent_given_at' => $photo->consent_given_at,
            ],
        ]);
    }
     public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo'         => 'required|image|mimes:jpg,jpeg,png,webp',
            'consent_given' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;

        // Find existing record
        $photo = CustomerPhotoUpload::where('user_id', $userId)->first();

        $path = Helper::handleFileUpload(
            $request->file('photo'),
            'customer_photo_with_consent'
        );

        if ($photo) {

            // delete old file
            if ($photo->photo_path) {
                Storage::disk('public')->delete($photo->photo_path);
            }

            // update same record (ID remains same)
            $photo->update([
                'photo_path'       => $path,
                'consent_given'    => true,
                'consent_given_at' => now(),
            ]);

        } else {

            $photo = CustomerPhotoUpload::create([
                'user_id'          => $userId,
                'photo_path'       => $path,
                'consent_given'    => true,
                'consent_given_at' => now(),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Photo uploaded successfully',
            'data' => $photo
        ]);
    }
}
