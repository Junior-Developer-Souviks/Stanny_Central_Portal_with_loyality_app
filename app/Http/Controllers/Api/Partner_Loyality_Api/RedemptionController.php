<?php

namespace App\Http\Controllers\Api\Partner_Loyality_Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\RedemptionRule;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
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

                'pin_number'  => Helper::decryptData($customer->pin),
                
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
                'customer_id'=>'required|exists:users,id',
                'pin'      => 'required|digits:5',
                'quantity' => 'required|min:1',
            ]);
    
            $quantity = $validated['quantity'] ?? 1;
    
            // STEP 2: Logged In Staff
            $staff = auth('api')->user();
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
            // $customer = User::where('qr_code', $validated['qr_code'])
            //     ->where('user_type', 1)
            //     ->lockForUpdate()
            //     ->first();

            $customer = User::lockForUpdate()
                        ->find($validated['customer_id']);
           
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
                // dd($deductPoints);

                if ($deductPoints <= 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid redeem calculation'
                    ], 422);
                }
    
                // Deduct
                $pointsBefore = $customer->total_points;

                $customer->total_points -= $deductPoints;
                $customer->save();

                $pointsAfter = $customer->total_points;
                
                WalletTransaction::create([
                    'user_id' => $customer->id,

                    'type' => 'debit',

                    // point ledger
                    'points' => $deductPoints,

                    'balance_before' => $pointsBefore,
                    'balance_after'  => $pointsAfter,


                    // lounge empty
                    'lounge_before' => null,
                    'lounge_visits' => null,
                    'lounge_after'  => null,
                    'lounge_used'   => null,


                    'source' => 'point_redemption',
                    'channel' => $channel,

                    'redeemed_by' => $staff->id,

                    'expiry_date' => null,

                    'created_at' => now(),
                    'updated_at' => now()
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
                         'points_before' => $pointsBefore,
                        'points_used' => $deductPoints,
                        'points_after' => $pointsAfter,

                        'transaction_type' => 'debit',
                        'source' => 'point_redemption',
                        'redeemed_by' => $staff->name ?? null,
                        'date' => now()->format('Y-m-d H:i:s')
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

                $loungeBefore = $customer->lounge_visits_total - $customer->lounge_visits_used;
               
                // Deduct
                $customer->lounge_visits_used += $quantity;
                
                $customer->save();

                $loungeAfter =  $loungeBefore - $quantity;
                    
                
               WalletTransaction::create([
                    'user_id' => $customer->id,

                    'type' => 'debit',


                    // points empty
                    'points' => 0,

                    'balance_before' => null,
                    'balance_after' => null,


                    // lounge ledger
                    'lounge_before' => $loungeBefore,

                    'lounge_visits' => $quantity,

                    'lounge_after' => $loungeAfter,

                    'lounge_used' => $quantity,


                    'source' => 'lounge_redemption',

                    'channel' => $channel,

                    'redeemed_by' => $staff->id,


                    'created_at' => now(),
                    'updated_at' => now()
                ]);
    
                DB::commit();
    
                return response()->json([
                    'status' => true,
                    'message' => 'Lounge redeemed successfully',
                    'data' => [
                        'customer_name' => $customer->name,

                        'channel' => $channel,
                        'redeem_type' => $redeemType,

                        'lounge_before' => $loungeBefore,
                        'lounge_used' => $quantity,
                        'lounge_after' => $loungeAfter,

                        'transaction_type' => 'debit',
                        'source' => 'lounge_redemption',

                        'date' => now()->format('Y-m-d H:i:s')
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
