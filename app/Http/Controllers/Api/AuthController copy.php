<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Otp;
use App\Models\UserLogin;
use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function CountryList(){
        $data = Country::select('title', 'country_code', 'mobile_length')->orderBy('title', 'ASC')->get();
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
                'message' => $validator->errors()
            ], 422);
        }

        $userLogin = UserLogin::where('device_id', $request->device_id)->first();

        if ($userLogin) {
            return response()->json([
                'message' => 'Device found, use MPIN to login',
                'show_mpin' => true
            ], 200);
        }

        return response()->json([
            'message' => 'Device not registered, login with OTP first',
            'show_mpin' => false
        ], 200);
    }

    public function userLogin(Request $request){
        $validator = Validator::make($request->all(),[
            'country_code' => 'required',
            'mobile' => 'required|exists:users,phone',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // Check if the user already exists in user_logins
        $userLogin = UserLogin::where('country_code', $request->country_code)
         ->where('mobile', $request->mobile)
         ->first();
         $user = User::where('country_code', $request->country_code)
         ->where('phone', $request->mobile)
         ->first();

        if ($userLogin && $userLogin->is_verified) {
            return response()->json([
                'message' => 'User already verified, use MPIN to login',
                'show_mpin' => true
            ], 200);
        }

        // Generate and store OTP
        // $otp = rand(1000, 9999);
        $otp = 1234;
        UserLogin::updateOrCreate(
            ['user_id'=>$user->id,'country_code' => $request->country_code, 'mobile' => $request->mobile],
            ['otp' => $otp, 'device_id' => $request->device_id]
        );

        // Send OTP (Replace with SMS API)
        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'otp' => $otp // Remove in production
        ], 200);
    }

    public function verifyOtp(Request $request){

            $validator = Validator::make($request->all(), [
                'country_code' => 'required',
                'mobile' => 'required|exists:users,phone',
                'otp' => 'required|digits:4',
                'device_id' => 'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 422);
            }
    
            $userLogin = UserLogin::where('country_code', $request->country_code)
                ->where('mobile', $request->mobile)
                ->where('otp', $request->otp)
                ->first();
    
            if (!$userLogin) {
                return response()->json([
                    'status'=>false,
                    'message' => 'Invalid OTP'
                ], 401);
            }
    
            $userLogin->is_verified = true;
            $userLogin->otp = null;
            $userLogin->device_id = $request->device_id;
            $userLogin->save();
    
            return response()->json([
                'status'=>true,
                'message' => 'OTP verified successfully. Please set MPIN.',
            ], 200);
    }

    /**
     * Step 4: Set MPIN
     */
    public function setMpin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|exists:users_logins,mobile',
            'mpin' => 'required|digits:4',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $userLogin = UserLogin::where('mobile', $request->mobile)->first();
        if (!$userLogin) {
            return response()->json([
                'status'=>false,
                'message' => 'User not found'
            ], 404);
        }

        $userLogin->mpin = Hash::make($request->mpin);
        $userLogin->save();

        return response()->json([
            'status'=>true,
            'message' => 'MPIN set successfully',
        ], 200);
    }

     /**
     * Step 5: Login with MPIN and Device ID
     */
    public function mpinLogin(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|exists:users_logins,mobile',
            'mpin' => 'required|digits:4',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $userLogin = UserLogin::where('mobile', $request->mobile)
            ->where('device_id', $request->device_id)
            ->first();

        if (!$userLogin || !Hash::check($request->mpin, $userLogin->mpin)) {
            return response()->json([
                'status'=>false,
                'message' => 'Invalid MPIN or Device ID'
            ], 401);
        }

        $user->tokens()->delete();
        // Generate API token
        $user = $userLogin->user; // Assuming `user_id` is linked to `users` table
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status'=>true,
            'message' => 'MPIN login successful',
            'token' => $token
        ], 200);
    }

    public function logout(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|exists:users_logins,mobile',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $userLogin = UserLogin::where('mobile', $request->mobile)
            ->where('device_id', $request->device_id)
            ->first();

        if (!$userLogin) {
            return response()->json(['message' => 'User not found or already logged out'], 404);
        }

        // Remove device ID to require OTP on next login
        $userLogin->device_id = null;
        $userLogin->mpin = null; // Optional: Remove MPIN if required
        $userLogin->save();

        // Delete API tokens if the user is authenticated
        if (Auth::check()) {
            Auth::user()->tokens()->delete(); // Logs out by deleting all tokens
        }
        return response()->json([
            'message' => 'Logout successful. Next login will require OTP.'
        ], 200);
    }

    // public function generateOtp(Request $request)
    // {
    //     try {
    //         // Validate the incoming request
    //         $rules = [
    //             'phone' => 'nullable|required_without_all:email,employee_id',
    //             'email' => 'nullable|email|required_without_all:phone,employee_id',
    //             'employee_id' => 'nullable|required_without_all:phone,email',
    //             'password' => 'required',
    //         ];
    //         $validator = Validator::make($request->all(), $rules);

    //         // Return error response if validation fails
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Validation failed',
    //                 'errors' => $validator->errors(),
    //             ]);
    //         }
    //         $user = null;
    
    //         $user = User::where(function ($query) use ($request) {
    //             if ($request->email) {
    //                 $query->where('email', $request->email);
    //             }
    //             if ($request->phone) {
    //                 $query->orWhere('phone', $request->phone);
    //             }
    //             if ($request->employee_id) {
    //                 $query->orWhere('employee_id', $request->employee_id);
    //             }
    //         })->where('user_type', 0)->where('business_type', 1)->first();
    
    //         // If no user found, return error response
    //         if (!$user) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'User not found with the provided details.',
    //             ]);
    //         }
    
    
    //         if (!Hash::check($request->password, $user->password)) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Invalid password',
    //             ]);
    //         }
    
    //         $otp = rand(100000, 999999);
    //         $expiresAt = now()->addMinutes(5);
    
    //         Otp::updateOrCreate(
    //             [
    //                 'phone' => $request->phone ?? $user->phone,
    //                 'email' => $request->email ?? $user->email,
    //                 'employee_id' => $request->employee_id ?? $user->employee_id, 
    //             ],
    //             [
    //                 'otp' => $otp,
    //                 'expires_at' => $expiresAt,
    //             ]
    //         );
    
    //         $user->otp_verification = 1;  // OTP not verified
    //         $user->save();
    
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'OTP generated successfully',
    //             'otp' => $otp, 
    //         ]);
    
    //     } catch (\Exception $e) {
    //         // dd($e);
    //         Log::error('OTP generation error: ' . $e->getMessage());
    
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'An error occurred while generating the OTP',
    //         ]);
    //     }
    // }
    
    


    // public function verifyOtp(Request $request)
    // {
    //     $request->validate([
    //         'phone' => 'required|digits:8',
    //         'otp' => 'required|digits:6',
    //     ]);

    //     $otpRecord = Otp::where('phone', $request->phone)
    //         ->where('otp', $request->otp)
    //         ->where('expires_at', '>=', now())
    //         ->first();

    //     if (!$otpRecord) {
    //         return response()->json([ 'status' => false,'message' => 'Invalid or expired OTP']);
    //     }

    //     // Retrieve or create the user
    //     $user = User::firstOrCreate(['phone' => $request->phone], [
    //         'password' => bcrypt('default_password'), // Replace this with a secure default password if needed
    //     ]);

    //     // Delete the OTP after successful verification
    //     $otpRecord->delete();

    //     // Generate Sanctum token
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'OTP verified successfully',
    //         'token' => $token,
    //         'user' => ,
    //     ]);
    // }

    // public function verifyOtp(Request $request)
    // {
    //     try {
    //         // Validate the incoming request 
    //         $rules = [
    //             'otp' => 'required|digits:6',
    //             'phone' => 'nullable|required_without_all:email,employee_id',
    //             'email' => 'nullable|email|required_without_all:phone,employee_id',
    //             'employee_id' => 'nullable|required_without_all:phone,email',
    //         ];
    //         $validator = Validator::make($request->all(), $rules);

    //         // Return error response if validation fails
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Validation failed',
    //                 'errors' => $validator->errors(),
    //             ]);
    //         }
    //         // Retrieve the OTP record from the database
    //         $otpRecord = Otp::where(function ($query) use ($request) {
    //             if ($request->email) {
    //                 $query->where('email', $request->email);
    //             }
    //             if ($request->phone) {
    //                 $query->orWhere('phone', $request->phone);
    //             }
    //             if ($request->employee_id) {
    //                 $query->orWhere('employee_id', $request->employee_id);
    //             }
    //         })->where('otp', $request->otp)
    //           ->where('expires_at', '>=', now())
    //           ->first();
    
    //         // Check if OTP is valid
    //         if (!$otpRecord) {
    //             return response()->json(['status' => false, 'message' => 'Invalid or expired OTP']);
    //         }
    
    //         // Retrieve the user based on email or phone from OTP record
    //         $user = null;
    //         if ($otpRecord->email) {
    //             $user = User::where('email', $otpRecord->email)->first();
    //         } elseif ($otpRecord->phone) {
    //             $user = User::where('phone', $otpRecord->phone)->first();
    //         } elseif ($otpRecord->employee_id) {
    //             $user = User::where('employee_id', $otpRecord->employee_id)->first();
    //         }
    
    //         // Update OTP verification status and save IP address
    //         $user->otp_verification = 2;  // OTP verified
    //         $user->ip_address = request()->userAgent();
    //         $user->save();
    
    //         // Delete the OTP record after successful verification
    //         $otpRecord->delete();
    
    //         // Optionally generate a Sanctum token for authentication
    //         // $token = $user->createToken('auth_token')->plainTextToken;
    
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'OTP verified successfully',
    //             'user' => $user,
    //         ]);
    
    //     } catch (\Exception $e) {
    //         // Log the exception message for debugging purposes
    //         Log::error('OTP verification error: ' . $e->getMessage());
    
    //         // Return a generic error response
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'An error occurred during OTP verification',
    //         ]);
    //     }
    // }
    

    // public function sendResetLink(Request $request)
    // {
    //     // Validate the email input
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|exists:users,email',
    //     ]);

    //     // Return error response if validation fails
    //     if ($validator->fails()) {
    //         return response()->json(['status' => 'false', 'message' => 'Invalid email address'], 400);
    //     }

    //     // $status = Password::sendResetLink($request->only('email'));
    //     // return $status === Password::RESET_LINK_SENT
    //     //     ? response()->json(['status' => 'true', 'message' => 'Reset link sent to your email'])
    //     //     : response()->json(['status' => 'false', 'message' => 'Failed to send reset link'], 500);
    //     $password=rand(100000, 999999);
    //     $hashedPassword = Hash::make($password);

    //     $user = User::where('email', $request->email)->first();
    //     $user->update(['password' => $hashedPassword]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'OTP generated successfully',
    //         'password' => $password, // Include this for testing purposes only
    //     ]);

    // }

    public function sendResetLink(Request $request)
    {
        try {
            // Validate that either email or phone is provided
            $rules = [
                'email' => 'required',
                // 'email' => 'nullable|email|exists:users,email|required_without:phone',
                // 'phone' => 'nullable|exists:users,phone|required_without:email',
            ];
            $validator = Validator::make($request->all(), $rules);

            // Return error response if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]);
            }
            // Generate a random 6-digit password
            $password = Str::random(6);

            // Retrieve the user based on email or phone
            $user = User::where(function ($query) use ($request) {
                if ($request->email) {
                    $query->where('email', $request->email);
                }
                // if ($request->phone) {
                //     $query->orWhere('phone', $request->phone);
                // }
            })->first();

            // Check if the user exists
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found']);
            }

            // Update the user's password
            $user->update(['password' =>$password]); // Ensure password is hashed

            // Simulate sending the new password (replace with actual email/SMS service)
            if ($request->email) {
                // Replace with your email sending logic
                // Mail::to($user->email)->send(new ResetPasswordMail($password));
            } elseif ($request->phone) {
                // Replace with your SMS sending logic
                // SendOtpService::send($user->phone, "Your new password is: $password");
            }

            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully. Check your email for the new password.',
                'password' => $password, // For testing purposes only, remove in production
            ]);
        
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Password reset error: ' . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while resetting the password. Please try again later.',
            ]);
        }
    }




    // public function loginWithMpin(Request $request)
    // {
    //     // Validate MPIN
    //     $request->validate([
    //         'mpin' => 'required|numeric',
    //         'email' => 'nullable|email|exists:users,email|required_without:phone',
    //         'phone' => 'nullable|exists:users,phone|required_without:email',
    //     ]);
    
    //     // Get the IP address of the user
    //     $ip_address = request()->userAgent();
    
    //     // Find user by email or phone and IP address
    //     $user = User::where(function ($query) use ($request, $ip_address) {
    //         if ($request->email) {
    //             $query->where('email', $request->email)->where('ip_address', $ip_address);
    //         }
    //         if ($request->phone) {
    //             $query->where('phone', $request->phone)->where('ip_address', $ip_address);
    //         }
    //     })->first();
    
    //     // If user not found, return error response
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not found with the provided details and IP address',
    //         ]);
    //     }
    
    //     // Check if MPIN exists; if not, save the provided MPIN
    //     if (!$user->mpin) {
    //         $user->mpin = $request->mpin;
    //         $user->save();
    //     } else {
    //         // Validate the MPIN
    //         $user = User::where('mpin', $request->mpin)->where('ip_address', $ip_address)->first();
    
    //         if (!$user) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Invalid MPIN',
    //             ]);
    //         }
    //     }
    
    //     // Generate Sanctum token
    //     $token = $user->createToken('MPIN-Login')->plainTextToken;
    
    //     // Set token expiration to 20 seconds
    //     $user->tokens()->latest('created_at')->first()->update([
    //         // 'expires_at' => now()->addSeconds(20),
    //         'expires_at' => now()->addHours(8),
            
    //     ]);
    
    //     // Return response with token
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Logged in successfully',
    //         'token' => $token,
    //     ]);
    // }
    
    // public function loginWithMpin(Request $request)
    // {
    //     try {
    //         // Validate input
    //         $rules = [
    //             'mpin' => 'required|numeric',

    //             // 'email' => 'nullable|email|exists:users,email|required_without:phone',
    //             // 'phone' => 'nullable|exists:users,phone|required_without:email',
    //         ];
        
    //         $validator = Validator::make($request->all(), $rules);

    //         // Return error response if validation fails
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Validation failed',
    //                 'errors' => $validator->errors(),
    //             ]);
    //         }
    //         // Retrieve user's IP address
    //         $ip_address = request()->userAgent();
            
    //         // Find user by email/phone and IP address
    //         $user = User::where('ip_address', $ip_address)
    //             // ->when($request->email, fn($query) => $query->where('email', $request->email))
    //             // ->when($request->phone, fn($query) => $query->where('phone', $request->phone))
    //             // ->when($request->phone, fn($query) => $query->where('phone', $request->phone))
    //             ->first();
        
    //         // If user not found, return error
    //         if (!$user) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'User not found with the provided details and IP address',
    //             ]);
    //         }
        
    //         // Handle MPIN logic
    //         if (!$user->mpin) {
    //             $user->mpin = Hash::make($request->mpin);;
    //             $user->save();
    //         } elseif (!Hash::check($request->mpin,$user->mpin)) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Invalid MPIN',
    //             ]);
    //         }
        
    //         // Generate Sanctum token
    //         $token = $user->createToken('MPIN-Login')->plainTextToken;
        
    //         // Update token expiration (requires `expires_at` column in `personal_access_tokens`)
    //         $user->tokens()->latest('created_at')->first()->update([
    //             'expires_at' => now()->addHours(8),
    //         ]);
        
    //         // Return success response
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Logged in successfully',
    //             'token' => $token,
    //         ]);
        
    //     } catch (\Exception $e) {
    //         // Log the exception for debugging purposes
    //         Log::error('Login with MPIN error: ' . $e->getMessage());

    //         // Return a generic error response
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'An error occurred during login. Please try again later.',
    //         ], 500);
    //     }
    // }



    public function loginWithMpin(Request $request)
    {
        try {
            // Validate input
            $rules = [
                'mpin' => 'required|numeric|digits:4',  // Ensures MPIN is exactly 4 digits long
                'phone' => 'nullable|required_without_all:email,employee_id',
                'email' => 'nullable|email|required_without_all:phone,employee_id',
                'employee_id' => 'nullable|required_without_all:phone,email',
            ];
        
            $validator = Validator::make($request->all(), $rules);

            // Return error response if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]);
            }

            // Retrieve the user's IP address (use userAgent here)
            $ip_address = request()->userAgent();
            
            // Find user by IP address (adjust based on your needs, e.g., email/phone if required)
            // $user = User::where('ip_address', $ip_address)->first();
            $user = User::where('ip_address', $ip_address)
                ->when($request->email, fn($query) => $query->where('email', $request->email))
                ->when($request->phone, fn($query) => $query->where('phone', $request->phone))
                ->when($request->employee_id, fn($query) => $query->where('employee_id', $request->employee_id))
                ->first();
            // If user is not found, return error
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found with the provided details and IP address',
                ]);
            }

            // Handle MPIN logic: if MPIN exists, validate it; otherwise, hash and save the new MPIN
            if (!$user->mpin) {
                // Hash the MPIN and save if not present
                $user->mpin = Hash::make($request->mpin);
                $user->save();
            } elseif (!Hash::check($request->mpin, $user->mpin)) {
                // If the provided MPIN does not match the stored hash
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid MPIN',
                ]);
            }

            // Generate Sanctum token for authenticated user
            $token = $user->createToken('MPIN-Login')->plainTextToken;

            // Optionally, update token expiration if needed
            $user->tokens()->latest('created_at')->first()->update([
                'expires_at' => now()->addHours(8),
            ]);

            // Return success response with the token
            return response()->json([
                'status' => true,
                'message' => 'Logged in successfully',
                'token' => $token,
            ]);
        
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Login with MPIN error: ' . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during login. Please try again later.',
            ]);
        }
    }
    // public function logout(Request $request)
    // {
    //     try {
    //         // Revoke the current user's token
    //         $request->user()->currentAccessToken()->delete();
    
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Logged out successfully',
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Logout error: ' . $e->getMessage());
    
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'An error occurred while logging out.',
    //         ], 500);
    //     }
    // }
    

    
    

}
