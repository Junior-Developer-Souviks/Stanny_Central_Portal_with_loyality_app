<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
       
        $user = User::find(auth()->guard('admin')->user()->id);
        if (!$user) {
            return response()->json(['status' => false,'message' => 'No data found']);
        }

        return response()->json([
            'status' => true,
            'message' => 'User details',
            'data' => $user, // Include this for testing purposes only
        ]);
    }
    
    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' =>  ['required', 'numeric', 'digits_between:' . config('app.phone_min_length') . ',' . config('app.phone_max_length')],
            'whatsapp_no' => ['required', 'numeric', 'digits_between:' . config('app.phone_min_length') . ',' . config('app.phone_max_length')],
            
            'dob' => 'required|date',
            'company_name' => 'nullable|string|max:255',
            'employee_rank' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|numeric|min:0',
            'gst_number' => 'nullable|string|max:15',
            'billing_address' => 'required|string',
            'billing_landmark' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_pin' => 'nullable|string|max:10',
            'shipping_address' => 'nullable|string', // Initially nullable
            'shipping_landmark' => 'nullable|string|max:255', // Initially nullable
            'shipping_city' => 'nullable|string|max:255', // Initially nullable
            'shipping_state' => 'nullable|string|max:255', // Initially nullable
            'shipping_country' => 'nullable|string|max:255', // Initially nullable
            'shipping_pin' => 'nullable|string|max:10', // Initially nullable
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'verified_video' => 'nullable|file|mimes:mp4,avi,mkv|max:10240',
            'gst_certificate_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_billing_shipping_same' => 'nullable|boolean',
        ];

        // Add shipping address requirements if billing and shipping are not the same
        if ($request->input('is_billing_shipping_same') == 0) {
            $rules = array_merge($rules, [
                'shipping_address' => 'nullable|string',
                'shipping_city' => 'nullable|string|max:255',
                'shipping_state' => 'nullable|string|max:255',
                'shipping_country' => 'nullable|string|max:255',
                'shipping_pin' => 'nullable|string|max:10',
                'shipping_landmark' => 'nullable|string|max:255', 
            ]);
        }

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        // Return error response if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ]);
        }

        try {
            // Handle file uploads
            $profileImagePath = $request->hasFile('profile_image')
                ? 'storage/' . $request->file('profile_image')->store('profile_images', 'public')
                : null;

            $verifiedVideoPath = $request->hasFile('verified_video')
                ? 'storage/' . $request->file('verified_video')->store('verified_videos', 'public')
                : null;

            $gstCertificatePath = $request->hasFile('gst_certificate_image')
                ? 'storage/' . $request->file('gst_certificate_image')->store('gst_certificates', 'public')
                : null;

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'whatsapp_no' => $request->whatsapp_no,
                'is_phone_whatsapp' => $request->is_phone_whatsapp,
                'country_code_alt_1' => $request->country_code_alt_1,
                'alternative_phone_number_1' => $request->alternative_phone_number_1,
                'is_alternate_no_whatsapp' => $request->is_alternate_no_whatsapp,
                'country_code_alt_2' => $request->country_code_alt_2,
                'alternative_phone_number_2' => $request->alternative_phone_number_2,
                'is_alternate_no2_whatsapp' => $request->is_alternate_no2_whatsapp,
                'dob' => $request->dob,
                'company_name' => $request->company_name,
                'employee_rank' => $request->employee_rank,
                'credit_limit' => $request->credit_limit ?? 0,
                'credit_days' => $request->credit_days ?? 0,
                'gst_number' => $request->gst_number,
                'profile_image' => $profileImagePath,
                'verified_video' => $verifiedVideoPath,
                'gst_certificate_image' => $gstCertificatePath,
            ]);

            // Save billing address
            UserAddress::create([
                'user_id' => $user->id,
                'address_type' => 1, // Billing address
                'address' => $request->billing_address,
                'landmark' => $request->billing_landmark,
                'city' => $request->billing_city,
                'state' => $request->billing_state,
                'country' => $request->billing_country,
                'pin' => $request->billing_pin,
            ]);

            // Determine and save shipping address
            $shippingData = $request->is_billing_shipping_same
                ? [
                    'address' => $request->billing_address,
                    'landmark' => $request->billing_landmark,
                    'city' => $request->billing_city,
                    'state' => $request->billing_state,
                    'country' => $request->billing_country,
                    'pin' => $request->billing_pin,
                ]
                : [
                    'address' => $request->shipping_address,
                    'landmark' => $request->shipping_landmark,
                    'city' => $request->shipping_city,
                    'state' => $request->shipping_state,
                    'country' => $request->shipping_country,
                    'pin' => $request->shipping_pin,
                ];

            UserAddress::create(array_merge($shippingData, [
                'user_id' => $user->id,
                'address_type' => 2, // Shipping address
            ]));

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Customer information saved successfully!',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            // Log error and return response
            \Log::error('Error saving customer: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while saving the customer information.',
            ]);
        }
    }

    public function list(Request $request)
    {
        $users = User::with(['billingAddress','shippingAddress'])  
            ->paginate(10);
        if($users){
            return response()->json([
                'status' => true,
                'message' => 'User list fetched successfully!',
                'data' => $users,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No any user list fetched!',
                'data' => $users,
            ]);
        }
    }
 
    public function search(Request $request)
    {
        // Fetch query parameters
        $query = User::query();

        // Search by name
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        // Search by company name
        if ($request->filled('company_name')) {
            $query->where('company_name', 'LIKE', '%' . $request->company_name . '%');
        }

        // Search by phone  
        if ($request->filled('phone')) {
            $query->where('phone', 'LIKE', '%' . $request->phone . '%');
        }

        // Search by city (billing or shipping)
        // if ($request->filled('city')) {
        //     $query->whereHas('addresses', function ($q) use ($request) {
        //         $q->where('city', 'LIKE', '%' . $request->city . '%');
        //     });
        // }

        // Include related addresses
        $users = $query->paginate(10);

        // Return the response
        if($users){
            return response()->json([
                'status' => true,
                'message' => 'Search results fetched successfully!',
                'data' => $users,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Sorry, We can not found any result!',
                'data' => $users,
            ]); 
        }
        
    }
    public function show($id)
    {
        try {
            // Retrieve the user with related addresses
            $user = User::with(['billingAddress','shippingAddress'])->findOrFail($id);
            
                return response()->json([
                    'status' => true,
                    'message' => 'User details fetched successfully!',
                    'data' => $user,
                ]);
           
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ]);
        }
    }

}
