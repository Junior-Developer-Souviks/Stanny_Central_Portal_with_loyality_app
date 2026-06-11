<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class CategoryController extends Controller
{
    
    protected function getAuthenticatedUser()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return $user;
    }
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }

        $data = Category::where('status',1)->get();
        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Category list fetched successfully!',
                'data' => $data,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No data found!'
            ]);
        }
       
    }

    public function getCategoriesByCollection($collectionId)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }

        try {
            // Fetch categories based on the collection_id
            $categories = Category::where('collection_id', $collectionId)->with('collection')
                ->where('status', 1) // Optional: Only active categories
                // ->select('id', 'short_code', 'title', 'image', 'status')
                ->get();

            if ($categories->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No categories found for the given collection ID.',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Collection wise categories list fetched successfully.',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching categories.',
                'error' => $e->getMessage(),
            ]);
        }
    }

}
