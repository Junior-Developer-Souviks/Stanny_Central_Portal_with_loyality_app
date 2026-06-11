<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Otp;
use App\Models\Order;
use App\Models\UserLogin;
use App\Models\Ledger;
use App\Models\Country;
use App\Models\BusinessType;
use App\Models\PaymentCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function CountryList(){
        $data = Country::select('id', 'title', 'country_code', 'mobile_length')->orderBy('title', 'ASC')->where('status', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Country list retrieved successfully',
            'countries' => $data,
        ], 200);
    }
    public function CountryDetailsByID($id){
        $data = Country::select('title', 'country_code', 'mobile_length')->find($id);

        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Country not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Country data retrieved successfully',
            'country' => $data,
        ], 200);
    }

    // User Login
    public function checkDevice(Request $request){
        $validator = Validator::make($request->all(), [
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userLogin = UserLogin::where('device_id', $request->device_id)->first();

        if ($userLogin) {
            return response()->json([
                'message' => 'Device found, use MPIN to login',
                'data'=>$userLogin,
                'show_mpin' => true
            ], 200);
        }

        return response()->json([
            'message' => 'Device not registered, login with OTP first',
            'show_mpin' => false
        ], 200);
    }

    // public function userLogin(Request $request){
    //    // dd('hi');
    //     $validator = Validator::make($request->all(),[
    //         'country_code' => 'required',
    //         'mobile' => [
    //         'required',
    //         function ($attribute, $value, $fail) {
    //             $exists = User::where('phone', $value)
    //                         ->where('user_type', 0)
    //                         ->exists();

    //             if (! $exists) {
    //                 $fail('The selected mobile number is invalid or does not belong to a valid user.');
    //             }
    //         },
    //     ],
    //         'device_id' => 'required'
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first(), // Returns only the first error message
    //         ], 422);
    //     }

    //     // Check if the user already exists in user_logins
    //     $userLogin = UserLogin::where('country_code', $request->country_code)
    //      ->where('mobile', $request->mobile)
    //      ->first();
    //      $user = User::where('country_code_phone', $request->country_code)
    //      ->where('phone', $request->mobile)
    //      ->first();

    //     if ($userLogin && $userLogin->is_verified) {
    //         return response()->json([
    //             'message' => 'User already verified, use MPIN to login',
    //             'show_mpin' => true
    //         ], 200);
    //     }

    //     // Generate and store OTP
    //     // $otp = rand(1000, 9999);
    //     $otp = 1234;
    //     UserLogin::updateOrCreate(
    //         ['user_id'=>$user->id,'country_code' => $request->country_code, 'mobile' => $request->mobile],
    //         ['otp' => $otp, 'device_id' => $request->device_id]
    //     );

    //     // Send OTP (Replace with SMS API)
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'OTP sent successfully',
    //         'otp' => $otp // Remove in production
    //     ], 200);
    // }

    // Step 1:
    public function userLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required|min:6',
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)
                    ->where('user_type', 0)
                    ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        // Generate OTP
        // $otp = rand(1000, 9999); // use random in prod
        $otp = 1234; // testing only

        UserLogin::updateOrCreate(
            ['user_id' => $user->id],
            [
                'email'      => $user->email,
                'otp'        => $otp,
                'device_id'  => $request->device_id,
                'is_verified'=> false,
            ]
        );

        // TODO: Send OTP via email
        // Mail::to($user->email)->send(new LoginOtpMail($otp));

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your email',
            'otp' => $otp, 
        ], 200);
    }


    // public function verifyOtp(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'country_code' => 'required',
    //         'mobile' => 'required|exists:users,phone',
    //         'otp' => 'required|digits:4',
    //         'device_id' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first(),
    //         ], 422);
    //     }
        
    //     $userLogin = UserLogin::where('country_code', $request->country_code)
    //         ->where('mobile', $request->mobile)
    //         ->where('otp', $request->otp)
    //         ->first();

    //     if (!$userLogin) {
    //         return response()->json([
    //             'status'=>false,
    //             'message' => 'Invalid OTP'
    //         ], 401);
    //     }

    //     $userLogin->is_verified = true;
    //     $userLogin->otp = null;
    //     $userLogin->device_id = $request->device_id;
    //     $userLogin->save();

    //     return response()->json([
    //         'status'=>true,
    //         'message' => 'OTP verified successfully. Please set MPIN.',
    //     ], 200);
    // }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $login = UserLogin::where('email', $request->email)
                        ->where('otp', $request->otp)
                        ->first();

        if (! $login) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP',
            ], 401);
        }

        $login->is_verified = true;
        $login->save();

        $user = User::find($login->user_id);

        // Optional token
        // $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            // 'token' => $token,
        ], 200);
    }

    /**
     * Step 5: Set MPIN
     */
    public function setMpin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:user_logins,email',
            'mpin' => 'required|digits:4',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userLogin = UserLogin::where('email', $request->email)->first();
        if (!$userLogin) {
            return response()->json([
                'status'=>false,
                'message' => 'User not found'
            ], 404);
        }

        $userLogin->mpin = Hash::make($request->mpin);
        $userLogin->save();

            // return response()->json([
            //     'status'=>true,
            //     'message' => 'MPIN set successfully',
            // ], 200);

        if (!$userLogin || !Hash::check($request->mpin, $userLogin->mpin)) {
            return response()->json([
                'status'=>false,
                'message' => 'Invalid MPIN or Device ID'
            ], 401);
        }

        $userLogin->device_id = $request->device_id;
        $userLogin->save();
        // Generate API token
        $user = $userLogin->user; // Assuming `user_id` is linked to `users` table
        $user->tokens()->delete();
        $token = $user->createToken('Login API')->plainTextToken;
        $data=[
            'id' => $user->id,
            'firstname' => $user->name,
            'surname' => $user->surname ?? '', // Avoid errors if surname is null
            'designation' => optional($user->designationDetails)->name ?? 'N/A', // Check if relation exists
            'email' => $user->email,
            'mobile' => $user->phone,
            'country_code' => $user->country_code_phone,
        ];
        return response()->json([
            'status'=>true,
            'message' => 'MPIN set with login successful',
            'token' => $token,
            'user' => $data
        ], 200);
    }

     /**
     * Step 6: Login with MPIN and Device ID
     */
    public function mpinLogin(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:user_logins,email',
            'mpin' => 'required|digits:4',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userLogin = UserLogin::where('email', $request->email)
            ->first();

        if (!$userLogin || !Hash::check($request->mpin, $userLogin->mpin)) {
            return response()->json([
                'status'=>false,
                'message' => 'Invalid MPIN or Device ID'
            ], 401);
        }

        $userLogin->device_id = $request->device_id;
        $userLogin->save();
        // Generate API token
        $user = $userLogin->user; // Assuming `user_id` is linked to `users` table
        $user->tokens()->delete();
        $token = $user->createToken('Login API')->plainTextToken;
        
      
        
       
        
        $data=[
            'id' => $user->id,
            'firstname' => $user->name,
            'surname' => $user->surname ?? '', // Avoid errors if surname is null
            'designation' => optional($user->designationDetails)->name ?? 'N/A', // Check if relation exists
            'email' => $user->email,
            'mobile' => $user->phone,
            'country_code' => $user->country_code_phone,
            
        ];
        return response()->json([
            'status'=>true,
            'message' => 'MPIN login successful',
            'token' => $token,
            'user' => $data
        ], 200);
    }

        
        public function logout(Request $request)
        {
          
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }
        
            // Make device_id null in user_logins table
            UserLogin::where('user_id', $user->id)
                ->update(['device_id' => null]);
                
                
           if (Auth::check()) {
               Auth::user()->tokens()->delete(); // Logs out by deleting all tokens
            }
        
            return response()->json([
                'status' => true,
                'message' => 'Logout successful.'
            ], 200);
        }
        
        public function forgotPassword(Request $request)
        {
            $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|exists:users,email',
            'user_id' => 'nullable|exists:users,id',
            'password' => 'required',
            ]);
         
            if ($validator->fails()) {
            return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            ], 422);
            }
         
        // Ensure either email or user_id is provided
            if (! $request->email && ! $request->user_id) {
            return response()->json([
            'status' => false,
            'message' => 'Email or User ID is required',
            ], 422);
            }
         
            // Fetch user
            $user = User::when($request->email, function ($q) use ($request) {
            $q->where('email', $request->email);
            })
            ->when($request->user_id, function ($q) use ($request) {
            $q->where('id', $request->user_id);
            })
            ->first();
         
            if (! $user) {
            return response()->json([
            'status' => false,
            'message' => 'User not found',
            ], 404);
            }
         
            // Update password
            $user->password = Hash::make($request->password);
            $user->save();
         
            return response()->json([
            'status' => true,
            'message' => 'Password updated successfully',
            ], 200);
        }

    /**
     * Step 2: Send OTP for Forgot MPIN
     */
    public function forgotMpin(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:user_logins,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Generate a 4-digit OTP
        // $otp = rand(1000, 9999);
        $otp = 1234;

        // Update OTP in the database
        $userLogin = UserLogin::where('email', $request->email)
        ->first();
        if ($userLogin) {
            $userLogin->otp = $otp;
            $userLogin->save();

            // TODO: Send OTP via SMS (Integrate SMS API here)

            return response()->json([
                'status' => true,
                'message' => 'OTP sent to your email.'
            ], 200);
        }

        return response()->json([
            'status' => false, 
            'message' => 'User not found.'
        ], 404);
    }

     /**
     * Step 3: Verify OTP for Forgot MPIN
     */
    public function verifyOtpMpin(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:user_logins,email',
            'otp'   => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userLogin = UserLogin::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();
        
        if (! $userLogin) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP.',
            ], 400);
        }

        // OTP verified successfully, clear OTP and allow reset MPIN
        $userLogin->update([
            'otp' => null,
        ]);

        return response()->json([
            'status' => true, 
            'message' => 'OTP verified. You can now reset MPIN.'
        ], 200);
    }

     /**
     * Step 4: Reset MPIN After OTP Verification
     */
    public function resetMpin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:user_logins,email',
            'new_mpin' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false, 
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userLogin = UserLogin::where('email', $request->email)
            ->first();

        if (!$userLogin) {
            return response()->json(['status' => false, 'message' => 'User not found.'], 404);
        }

        // Hash the MPIN before saving
        $userLogin->mpin = Hash::make($request->new_mpin);
        $userLogin->save();

        return response()->json(['status' => true, 'message' => 'MPIN reset successfully.'], 200);
    }

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
    public function profile(){
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }

        return response()->json([
            'status' => true,
            'message' => 'User profile retrieved successfully',
            'user' => [
                'id' => $user->id,
                'firstname' => $user->name,
                'surname' => $user->surname ?? '', // Avoid errors if surname is null
                'designation' => optional($user->designationDetails)->name ?? 'N/A', // Check if relation exists
                'email' => $user->email,
                'mobile' => $user->phone,
                'country_code' => $user->country_code_phone,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    public function dashboard(){
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }
        
        $startDate = Carbon::today()->startOfDay();
        $endDate   = Carbon::today()->endOfDay();
        
        // Today's Sales
        $totalSales = Order::where('created_by', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');


        // Today's Collection
            $totalCollections = PaymentCollection::where('is_approve', 1)
                ->where('user_id', $user->id)
                ->whereBetween('cheque_date', [$startDate, $endDate])
                ->sum('collection_amount');

        // Get All business type

        $totalBusinesstype = BusinessType::select('id', 'title', 'image')->orderBy('title', 'ASC')->get();

        return response()->json([
            'status' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'total_sales' => $totalSales,
                'total_collections' => $totalCollections,
                'total_business_type' => $totalBusinesstype
            ]
        ], 200);

    }

    public function customer_list(){
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }

        // Get All customer created by this user

        $customers = User::with('billingAddress')->where('created_by', $user->id)->orderBy('id','DESC')->get();
        // dd($customers);
        return response()->json([
            'status' => true,
            'message' => 'Customer list retrieved successfully',
            'customers' => $customers
        ], 200);
    }
    
    public function customer_details($id){
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }

        $details = User::with('billingAddress')->find($id);
      
        if (!$details) {
            return response()->json([
                'status' => false,
                'message' => 'Customer details not found',
            ], 404);
        }
        $latest_order = Order::select('id', 'order_number', 'total_amount','created_at')
        ->where('customer_id', $id)
        ->where('created_by', $user->id)
        ->with(['items' => function ($query) {
            $query->select('order_id', 'product_name'); // Fetch only relevant columns
        }])
        ->withCount('items') // Get the count of related items
        ->latest('id')
        ->get();
        $orders = [];
      
        if(count($latest_order)>0){
            foreach($latest_order as $key => $item){
                $orders[$key]['id'] =$item->id; 
                $orders[$key]['order_number'] =$item->order_number; 
                $orders[$key]['total_amount'] =$item->total_amount;
                $extra_item = count($item->items)==1?"":" +(".(count($item->items)-1)." Item)";
                $orders[$key]['products'] =count($item->items)==1?$item->items[0]->product_name.$extra_item:"N/A"; 
                $orders[$key]['order_date'] = date('d-m-y', strtotime($item->created_at)); 
            }
        }
       
        $ledgerCredit=Ledger::where('customer_id',$id)->where('is_credit',1)->sum('transaction_amount');
        $ledgerDebit=Ledger::where('customer_id',$id)->where('is_debit',1)->sum('transaction_amount');
        
        $data = [];
        $data['details']=$details;
        $data['latest_orders']=$orders;
        $data['wallet']=$ledgerCredit;
        $data['collectionAmount']=$ledgerDebit;
        return response()->json([
            'status' => true,
            'message' => 'Customer data retrieved successfully',
            'data' => $data,
        ], 200);
    }
    
    public function customer_filter(Request $request)
{
    // dd($request->all());
    $user = $this->getAuthenticatedUser();
    if ($user instanceof \Illuminate\Http\JsonResponse) {
        return $user;
    }

    // $filter = $request->keyword;
    $filter = $request->query('keyword');
    
    $users = User::with('billingAddress')
        ->where('user_type', 1)
        ->where('status', 1)
        ->where('created_by', $user->id)
        ->when($filter, function ($query) use ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%")
                  ->orWhere('phone', 'like', "%{$filter}%")
                  ->orWhere('whatsapp_no', 'like', "%{$filter}%")
                  ->orWhere('company_name', 'like', "%{$filter}%")
                  ->orWhere('email', 'like', "%{$filter}%")
                  ->orWhereHas('orders', function ($q2) use ($filter) {
                      $q2->where('order_number', 'like', "%{$filter}%");
                  });
            });
        })
        ->take(20) // optional limit
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Data fetched successfully!',
        'data' => $users,
    ], 200);
}

  
    public function customer_store(Request $request){
        $authUser = $this->getAuthenticatedUser();
        $phone_code_length = $request->phone_code_length;
        $whatsapp_code_length = $request->whatsapp_code_length;
        $alternative_phone_1_code_length = $request->alternative_phone_1_code_length;
        $alternative_phone_2_code_length = $request->alternative_phone_2_code_length;
        $rules = [
            'prefix' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone_code' => 'nullable|string|max:255',
            'phone' => [
                'nullable',
                    Rule::when($phone_code_length, [
                        'regex:/^\d{'. $phone_code_length .'}$/'
                    ]),
                    Rule::unique('users', 'phone')->whereNull('deleted_at'),
                ],
            'country_code_alt_1' => 'nullable|string|max:255',
            'alternative_phone_number_1' => [
                'nullable',
                'regex:/^\d{'. $alternative_phone_1_code_length .'}$/',
            ],

            'country_code_alt_2' => 'nullable|string|max:255',
            'alternative_phone_number_2' => [
                'nullable',
                'regex:/^\d{'. $alternative_phone_2_code_length .'}$/',
            ],
            'dob' => 'nullable|date',
            'company_name' => 'nullable|string|max:255',
            'employee_rank' => 'nullable|string|max:255',
            // 'gst_number' => 'nullable|string|max:15',
            'credit_limit' => 'nullable|numeric',
            'credit_days' => 'nullable|integer',
            'billing_address' => 'nullable|string',
            'billing_landmark' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_state' => 'nullable|string',
            'billing_country' => 'nullable|string|max:255',
            'billing_pin' => 'nullable|string|max:10',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // 'verified_video' => 'nullable|file|mimes:mp4,avi,mkv|max:10240',
            'customer_badge_type' => 'nullable|in:general,premium',
        ];
        // Validate the request
        $validator = Validator::make($request->all(), $rules);
        // Return error response if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }
        DB::beginTransaction();

        try {
            $profileImagePath = $request->hasFile('profile_image')
                ? 'storage/' . $request->file('profile_image')->store('profile_images', 'public')
                : null;
           
            // Create the user
            $user = User::create([
                'prefix' => $request->prefix,
                'name' => $request->name,
                'email' => $request->email,
                'country_code_phone' => $request->phone_code,
                'phone' => $request->phone,
                'country_code_whatsapp' => $request->whatsapp_code,
                'whatsapp_no' => $request->whatsapp_no,
                'country_code_alt_1' => $request->country_code_alt_1,
                'alternative_phone_number_1' => $request->alternative_phone_number_1,
                'country_code_alt_2' => $request->country_code_alt_2,
                'alternative_phone_number_2'  => $request->alternative_phone_number_2,
                'dob' => $request->dob,
                'company_name' => $request->company_name,
                'employee_rank' => $request->employee_rank,
                'profile_image' => $profileImagePath,
                'user_type' => 1,
                'customer_badge' => $request->customer_badge_type,
                'created_by' => $authUser->id,
                // 'verified_video' => $verifiedVideoPath,
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
                'zip_code' => $request->billing_pin,
            ]);
            DB::commit();

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Customer information saved successfully!',
                'user' => $user->load('userAddress'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function customer_update($id, Request $request){
        $phone_code_length = $request->phone_code_length;
        $whatsapp_code_length = $request->whatsapp_code_length;
        $alternative_phone_1_code_length = $request->alternative_phone_1_code_length;
        $alternative_phone_2_code_length = $request->alternative_phone_2_code_length;
        // dd($whatsapp_code_length);
        // Validation Rules
        $rules = [
            'prefix' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone_code' => 'required|string|max:10',
            'phone' => [
                'required',
                "regex:/^\d{{$phone_code_length}}$/",
            ],
           
            'country_code_alt_1' => 'nullable|string|max:255',
            'alternative_phone_number_1' => [
                'nullable',
                'regex:/^\d{'. $alternative_phone_1_code_length .'}$/',
            ],

            'country_code_alt_2' => 'nullable|string|max:255',
            'alternative_phone_number_2' => [
                'nullable',
                'regex:/^\d{'. $alternative_phone_2_code_length .'}$/',
            ],
            
            'dob' => 'nullable|date',
            'company_name' => 'nullable|string|max:255',
            'employee_rank' => 'nullable|string|max:255',
            'billing_address' => 'required|string',
            'billing_landmark' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_pin' => 'nullable|string|max:10',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'verified_video' => 'nullable|file|mimes:mp4,avi,mkv|max:10240',
        ];

        // Validate input data
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Find user by ID
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found!',
                ], 404);
            }

            // Handle Profile Image Upload
            if ($request->hasFile('profile_image')) {
                // Delete the old profile image if exists
                if ($user->profile_image && Storage::exists($user->profile_image)) {
                    Storage::delete($user->profile_image);
                }
                $profileImagePath = 'storage/' . $request->file('profile_image')->store('profile_images', 'public');
            } else {
                $profileImagePath = $user->profile_image;
            }

            // Handle Verified Video Upload
            if ($request->hasFile('verified_video')) {
                if ($user->verified_video && Storage::exists($user->verified_video)) {
                    Storage::delete($user->verified_video);
                }
                $verifiedVideoPath = 'storage/' . $request->file('verified_video')->store('verified_videos', 'public');
            } else {
                $verifiedVideoPath = $user->verified_video;
            }

            // Update user information
            $user->update([
                'prefix' => $request->prefix,
                'name' => $request->name,
                'email' => $request->email,
                'country_code_phone' => $request->phone_code,
                'phone' => $request->phone,
                'country_code_whatsapp' => $request->whatsapp_code,
                'whatsapp_no' => $request->whatsapp_no,
                'country_code_alt_1' => $request->country_code_alt_1,
                'alternative_phone_number_1' => $request->alternative_phone_number_1,
                'country_code_alt_2' => $request->country_code_alt_2,
                'alternative_phone_number_2'  => $request->alternative_phone_number_2,
                'dob' => $request->dob,
                'company_name' => $request->company_name,
                'employee_rank' => $request->employee_rank,
                'profile_image' => $profileImagePath,
                'verified_video' => $verifiedVideoPath,
            ]);

            // Update or Create Billing Address
            UserAddress::updateOrCreate(
                ['user_id' => $user->id, 'address_type' => 1], // Billing Address Type
                [
                    'address' => $request->billing_address,
                    'landmark' => $request->billing_landmark,
                    'city' => $request->billing_city,
                    'country' => $request->billing_country,
                    'zip_code' => $request->billing_pin,
                ]
            );

            DB::commit();

            // Return Success Response
            return response()->json([
                'status' => true,
                'message' => 'Customer information updated successfully!',
                'user' => $user->load('billingAddress'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ]);
        }
    }

   
}
