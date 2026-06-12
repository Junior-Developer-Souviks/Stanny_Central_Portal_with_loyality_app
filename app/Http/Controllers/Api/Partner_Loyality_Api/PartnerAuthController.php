<?php

namespace App\Http\Controllers\Api\Partner_Loyality_Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class PartnerAuthController extends Controller
{
    public function login(Request $request)

        {

            // STEP 1: Validate input

            $validated = $request->validate([
                'phone'      => ['required', 'numeric'],
            ]);

        

            // STEP 2: Find user

        $user = User::where([
                'phone' => $validated['phone'],
                'user_type' => 0,
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