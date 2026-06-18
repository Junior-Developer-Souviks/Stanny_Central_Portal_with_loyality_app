<?php



namespace App\Http\Controllers\Api\Customer_Loyality_Api;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

use App\Models\Country;

use App\Models\User;

use App\Models\UserAddress;

use App\Models\Wallet;

use App\Models\Setting;

use App\Models\WalletTransaction;

use App\Helpers\Helper;





class AuthController extends Controller

{

    //  public function getPrefixes()

    // {

    //     return response()->json([

    //         'status' => true,

    //         'message' => 'Prefixes fetched successfully',

    //         'data' => Helper::getNamePrefixes()// your helper function

    //     ]);

    // }

    

    // public function country_code(){

    //     $countries = Country::where('status', 1)

    //         ->select('id', 'title', 'country_code', 'mobile_length')

    //         ->orderBy('title')

    //         ->get();



    //     return response()->json([

    //         'success' => true,

    //         'data'    => $countries,

    //     ], 200);

    // }

    
   

    
    // Self Register
    public function register(Request $request)
    {
        DB::beginTransaction();
    
        try {
    
            // STEP 1: Validate request
            $validated = $request->validate([
                'phone'      => [
                    'required',
                    'numeric',
                    'unique:users,phone'
                ],
            ]);
    
            // STEP 3: Check existing user
            $existingUser = User::where('phone', $validated['phone'])
                ->first();
    
            if ($existingUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'User already registered'
                ], 409);
            }
    
            // STEP 4: Generate PIN
            $plainPin = rand(10000, 99999);
            $welcomeBonus = (int) Setting::where('key', 'welcome_bonus')->value('value');
            
            // STEP 5: Create User
            $user = User::create([
                'phone'               => $validated['phone'],
                'type'                => 1,
                'qr_code'             => Str::uuid(),
                'card_number'         => 'CARD' . time() . rand(10, 99),
                'pin'                 => Helper::encryptData($plainPin),
                'total_points'        => $welcomeBonus
            ]);
    
            // STEP 6: Welcome Bonus Wallet Entry
            $expiryDays = (int) Setting::where('key', 'point_expiry_days')->value('value');
    
            WalletTransaction::create([
                'user_id'     => $user->id,
                'type'        => 'credit',
                'points'      => $user->total_points,
                'source'      => 'Welcome Bonus',
                'channel'      => 'Bonus Point',
                'expiry_date' => now()->addDays($expiryDays)
            ]);
    
            DB::commit();
    
         
            return response()->json([
                'status' => true,
                'message' => 'Customer registered successfully',
                'data' => [
                    'user_id'     => $user->id,
                    'phone'       => $user->phone,
                    'card_number' => $user->card_number,
                    'qr_code'     => $user->qr_code,
                    'points'      => $user->total_points,
                    'pin'         => $plainPin,
                ]
            ], 201);
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    

     public function login(Request $request)
    {
       
    
        $validated = $request->validate([
            'phone' => ['required', 'numeric'],
        ]);
    
        $user = User::where('phone', $validated['phone'])
            ->where('user_type', 1)
            ->first();
    
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
    
        // STATIC OTP
        $otp = 1234;
        $user->update([
            'otp' => $otp,
            'is_verified' => 0
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'otp' => $otp
        ]);
    }
    
    
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required',
            'otp' => 'required'
        ]);
    
        $user = User::where('phone', $validated['phone'])
            ->where('otp', $validated['otp'])
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP'
            ], 401);
        }
    
        $user->update([
            'is_verified' => 1,
            'otp_verified_at' => now(),
        ]);
    
       
    
        $token = $user->createToken('customer-token')->plainTextToken;
    
        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully',
            'data' => [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'token' => $token
            ]
        ]);
    }
    
    public function updateProfile(Request $request)
    {
       
        $user = auth('api')->user();

        $validated = $request->validate([
            'prefix' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'street_address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
        ]);
    
        // UPDATE USER
        $user->update([
            'prefix' => $validated['prefix'] ?? $user->prefix,
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
        ]);
    
        // ADDRESS UPDATE
        UserAddress::updateOrCreate(
            [
                'user_id' => $user->id,
                'address_type' => 1
            ],
            [
                'address' => $validated['street_address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? null,
            ]
        );
    
        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }


    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token'   => 'required|string',
            'device_type' => 'required|in:android,ios',
        ]);

        $request->user()->update([
            'fcm_token'   => $request->fcm_token,
            'device_type' => $request->device_type,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Token saved successfully'
        ]);
    }

}









  