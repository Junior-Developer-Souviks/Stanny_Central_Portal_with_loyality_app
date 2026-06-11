<?php

namespace App\Http\Controllers\Api\Partner_Loyality_Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;

class PartnerAuthController extends Controller
{
    public function login(Request $request)

        {

            // STEP 1: Get mobile length from country

            $mobileLength = Country::where('country_code', $request->phone_code)

                ->value('mobile_length');

        

            if (!$mobileLength) {

                return response()->json([

                    'status' => false,

                    'message' => 'Invalid country code'

                ], 422);

            }

        

            // STEP 2: Validate input

            $validated = $request->validate([

                'phone_code' => 'required|exists:countries,country_code',

                'phone'      => ['required', 'numeric', 'digits:' . $mobileLength],

            ], [

                'phone.digits' => "Phone number must be {$mobileLength} digits"

            ]);

        

            // STEP 3: Find user

        $user = User::where([
                'phone' => $validated['phone'],
                'country_code_phone' => $validated['phone_code'],
                'user_type' => 0,
                'designation' => 14,
            ])->first();

        

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
 
                    'name'    => $user->name,

                    'phone'   => $user->country_code_phone .' '.$user->phone,

                    'designation'   => $user->designationDetails ? $user->designationDetails->name : null,
                    
                    'token'  => $token
                ]
        ]);
    }
        
        
}