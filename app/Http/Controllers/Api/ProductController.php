<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\CatalogueTitle;
use App\Models\Measurement;
use App\Models\Page;
use App\Models\Catalogue;
use App\Models\PageItem;
use App\Models\Fabric;
use App\Models\CataloguePageItem;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class ProductController extends Controller
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
    public function getProductsByCategoryAndCollection(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }
        try {
            // Validate the query parameters
            $validated = $request->validate([
                'collection_id' => 'required|exists:collections,id',
                'category_id' => 'required|exists:categories,id',
            ], [
                'collection_id.required' => 'The collection ID is required.',
                'collection_id.exists' => 'The selected collection does not exist.',
                'category_id.required' => 'The category ID is required.',
                'category_id.exists' => 'The selected category does not exist.',
            ]);
    
            // Fetch products by category and collection
            $products = Product::where('collection_id', $validated['collection_id'])
                ->where('category_id', $validated['category_id'])
                ->get();
    
            // Check if products are found
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found for the given category and collection.',
                ]);
            }
    
            // Return the list of products
            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'data' => $products,
            ]);
        } catch (ValidationException $e) {
            // Handle validation exceptions with a custom error response
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching products.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getProductsByCollection(Request $request)
    {
        try {
            // Validate the query parameters
            $rules = [
                'collection_id' => 'required|exists:collections,id',
            ];
            
            $messages = [
                'collection_id.required' => 'The collection ID is required.',
                'collection_id.exists' => 'The selected collection does not exist.',
            ];
            
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]);
            }
            // Fetch products by category and collection
            $products = Product::where('collection_id', $validated['collection_id'])
                ->get();
    
            // Check if products are found
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found for the given collection.',
                ]);
            }
    
            // Return the list of products
            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'data' => $products,
            ]);
        } catch (ValidationException $e) {
            // Handle validation exceptions with a custom error response
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching products.',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    //catalogue list
    
    public function catalogueList(Request $request)
    {
        
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }
        
        $data = CatalogueTitle::get();
        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Catalogue list fetched successfully!',
                'data' => $data,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No data found!'
            ]);
        }
       
    }
    
    //catalogue wise pages
    public function pages(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }
        try {
            // Validate the query parameters
            $rules = [
                'catalogue_title_id' => 'required|exists:catalogue_titles,id',
            ];
            
            $messages = [
                'catalogue_title_id.required' => 'The catalogue ID is required.',
                'catalogue_title_id.exists' => 'The selected catalogue title does not exist.',
            ];
            
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]);
            }
            // Fetch products by category and collection
            $products = Catalogue::where('catalogue_title_id', $request['catalogue_title_id'])->with('catalogueTitle','pages')
                ->get();
    
            // Check if products are found
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No page found for the given catalogue.',
                ]);
            }
    
            // Return the list of products
            return response()->json([
                'status' => true,
                'message' => 'Pages retrieved successfully.',
                'data' => $products,
            ]);
        } catch (ValidationException $e) {
            // Handle validation exceptions with a custom error response
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching products.',
                'error' => $e->getMessage(),
            ]);
        }
    }
    //page wise item list
     public function pageItem(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }
        try {
            // Validate the query parameters
            $rules = [
                'catalogue_id' => 'required|exists:catalogues,id',
                'page_id' => 'required|exists:pages,id',
            ];
            
            $messages = [
                'catalogue_id.required' => 'The catalogue ID is required.',
                'catalogue_id.exists' => 'The selected catalogue id does not exist.',
            ];
            
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]);
            }
            // Fetch products by category and collection
            $products = CataloguePageItem::where('catalogue_id', $request['catalogue_id'])->where('page_id', $request['page_id'])->with('page','catalogue')
                ->get();
    
            // Check if products are found
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No page item found for the given catalogue.',
                ]);
            }
    
            // Return the list of products
            return response()->json([
                'status' => true,
                'message' => 'Page wise items retrieved successfully.',
                'data' => $products,
            ]);
        } catch (ValidationException $e) {
            // Handle validation exceptions with a custom error response
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching products.',
                'error' => $e->getMessage(),
            ]);
        }
    }
    //measurement list
    
     public function getMeasurementProductwise(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }
        try {
            // Validate the query parameters
            $rules = [
                'product_id' => 'required|exists:products,id',
            ];
            
            $messages = [
                'product_id.required' => 'The product ID is required.',
                'product_id.exists' => 'The selected product does not exist.',
            ];
            
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]);
            }
            // Fetch products by category and collection
            $products = Measurement::where('product_id', $request['product_id'])->where('status', 1)->with('product')->orderBy('position', 'ASC')->get();
    
            // Check if products are found
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No measurements found for the given product.',
                ]);
            }
    
            // Return the list of products
            return response()->json([
                'status' => true,
                'message' => 'Measurements retrieved successfully.',
                'data' => $products,
            ]);
        } catch (ValidationException $e) {
            // Handle validation exceptions with a custom error response
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching products.',
                'error' => $e->getMessage(),
            ]);
        }
    }
    //check product price fabric wise
    
    public function checkPrice(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }
        try {
            // Validate the query parameters
            $rules = [
                'fabric_id' => 'required|exists:fabrics,id',
                'price' => 'required',
            ];
            
            $messages = [
                'fabric_id.required' => 'The fabric ID is required.',
                'fabric_id.exists' => 'The selected fabric id does not exist.',
            ];
            
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]);
            }
            $fabricData = Fabric::find($request->fabric_id);
            if ($fabricData && floatval($request->price) < floatval($fabricData->threshold_price)) {
                 return response()->json([
                    'status' => false,
                    'message' => 'The price for fabric '.$fabricData->title.' cannot be less than its threshold price of '. $fabricData->threshold_price,
                    'data' => $fabricData,
                ]);
                
            }
            // Check if products are found
            if (empty($fabricData)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No page item found for the given catalogue.',
                ]);
            }
    
            // Return the list of products
            return response()->json([
                'status' => true,
                'message' => 'Price is matched with threshold price.',
                'data' => $fabricData,
            ]);
        } catch (ValidationException $e) {
            // Handle validation exceptions with a custom error response
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching products.',
                'error' => $e->getMessage(),
            ]);
        }
        

       
    }
    
    public function getAllProducts(){
        $products = Product::whereNull('deleted_at')->get();
        return response()->json([
                'status' => true,
                'message' => 'Products fetched successfully',
                'data' => $products
            ]);
    }

}
