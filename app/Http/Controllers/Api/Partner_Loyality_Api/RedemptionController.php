<?php

namespace App\Http\Controllers\Api\Partner_Loyality_Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RedemptionRule;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;


class RedemptionController extends Controller
{
    public function wallet($qr_code){
        $customer = User::where('qr_code', $qr_code)
            ->where('user_type', 1)
            ->first();
            
        if (!$customer) {

            return response()->json([
                'status' => false,
                'message' => 'Customer not found'
            ]);
        }
        
        return response()->json([
            'status' => true,

            'data' => [

                'customer_id' => $customer->id,

                'customer_name' => $customer->name,

                'card_number' => $customer->card_number,

                'pin_number'  => $customer->pin,
                
                'total_points' => $customer->total_points,

                'lounge_visits' =>
                    $customer->lounge_visits_total
                    -
                    $customer->lounge_visits_used,
            ]
        ]);
    }
    
    public function redeem(Request $request)
    {
        DB::beginTransaction();
    
        try {
    
            // STEP 1: Validate Request
            $validated = $request->validate([
                'qr_code'  => 'required',
                'pin'      => 'required|digits:5',
                'quantity' => 'required|integer|min:1',
            ]);
    
            $quantity = $validated['quantity'] ?? 1;
    
            // STEP 2: Logged In Staff
            $staff = auth()->user();
    
            /*
            DESIGNATION MAPPING
    
            14 = Airport Staff → Lounge
            15 = Grocery Staff → Points
            16 = Store Staff   → Points
            */
    
            if ($staff->designation == 14) {
    
                $channel = 'airport';
                $redeemType = 'lounge';
    
            } elseif ($staff->designation == 15) {
    
                $channel = 'grocery';
                $redeemType = 'points';
    
            } elseif ($staff->designation == 16) {
    
                $channel = 'store_sales';
                $redeemType = 'points';
    
            } else {
    
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid staff designation'
                ], 403);
            }
    
            // STEP 3: Find Customer
            $customer = User::where('qr_code', $validated['qr_code'])
                ->where('user_type', 1)
                ->lockForUpdate()
                ->first();
    
            if (!$customer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
    
            // STEP 4: Validate PIN
            if ($validated['pin'] != $customer->pin) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid PIN'
                ], 422);
            }
    
            /*
            ====================================================
                    POINT REDEMPTION
            ====================================================
            */
    
            if ($redeemType == 'points') {
    
                $rule = RedemptionRule::where('channel', $channel)
                    ->where('status', 1)
                    ->first();
    
                if (!$rule) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Redemption rule not found'
                    ], 404);
                }
    
                if ($customer->total_points <= 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No points available'
                    ], 422);
                }
    
                if ($quantity > $customer->total_points) {
                    return response()->json([
                        'status' => false,
                        'message' => "Only {$customer->total_points} point(s) available, you requested {$quantity}"
                    ], 422);
                }
    
                $totalPointsBefore = $customer->total_points;
    
                $deductPoints = round($quantity * $rule->ratio, 2);
    
                if ($deductPoints <= 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid redeem calculation'
                    ], 422);
                }
    
                // Deduct
                $customer->total_points -= $deductPoints;
                $customer->save();
    
                WalletTransaction::create([
                    'user_id'      => $customer->id,
                    'type'         => 'debit',
                    'points'       => $deductPoints,
                    'source'       => 'redemption',
                    'channel'      => $channel,
                    'reference_id' => $staff->id,
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
    
                DB::commit();
    
                return response()->json([
                    'status' => true,
                    'message' => 'Points redeemed successfully',
                    'data' => [
                        'customer_name' => $customer->name,
                        'channel' => $channel,
                        'redeem_type' => $redeemType,
                        'quantity_used' => $quantity,
                        'total_points_before' => $totalPointsBefore,
                        'deducted_points' => $deductPoints,
                        'remaining_points' => $customer->total_points
                    ]
                ]);
            }
    
            /*
            ====================================================
                    LOUNGE REDEMPTION
            ====================================================
            */
    
            if ($redeemType == 'lounge') {
    
                $availableVisits =
                    $customer->lounge_visits_total
                    -
                    $customer->lounge_visits_used;
    
                if ($availableVisits <= 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No lounge visits available'
                    ], 422);
                }
    
                if ($quantity > $availableVisits) {
                    return response()->json([
                        'status' => false,
                        'message' => "Only {$availableVisits} visit's available, you requested {$quantity}"
                    ], 422);
                }
                
                // Deduct
                $customer->lounge_visits_used += $quantity;
                
                $customer->save();
    
                WalletTransaction::create([
                    'user_id'       => $customer->id,
                    'type'          => 'debit',
                    'lounge_visits' => $quantity,
                    'source'        => 'lounge_redemption',
                    'channel'       => $channel,
                    'reference_id'  => $staff->id,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
    
                DB::commit();
    
                return response()->json([
                    'status' => true,
                    'message' => 'Lounge redeemed successfully',
                    'data' => [
                        'customer_name' => $customer->name,
                        'channel' => $channel,
                        'redeem_type' => $redeemType,
                        'quantity_used' => $quantity,
                        'remaining_lounge_visits' =>
                            $customer->lounge_visits_total
                            -
                            $customer->lounge_visits_used
                    ]
                ]);
            }
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'Redemption failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
}
