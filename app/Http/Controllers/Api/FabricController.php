<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fabric;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class FabricController extends Controller
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
    public function index(Request $request,$id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }
        $data = Fabric::where('collection_id',$id)->with('collection')->get();
        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Fabric list fetched successfully!',
                'data' => $data,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No data found!'
            ]);
        }
       
    }
}
