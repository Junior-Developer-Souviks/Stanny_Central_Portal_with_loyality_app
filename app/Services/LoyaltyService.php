<?php

namespace App\Services;

use App\Models\Order;
use App\Models\LoyaltyRule;
use App\Models\WalletTransaction;
use App\Models\User;
use Carbon\Carbon;

class LoyaltyService
{
    public function processInvoice($invoice)
    {
        $user = User::find($invoice->customer_id);

        if (!$user) return;

        // =========================
        // ONLY USERS WITH QR CODE
        // =========================
        if (empty($user->qr_code)) {
            return; // Skip users without QR code
        }

        // =========================
        // STEP 1: FULL PAYMENT CHECK
        // =========================
        if ($invoice->payment_status != 2) {
            return;
        }

        // =========================
        // STEP 2: GET ORDER (IMPORTANT FIX)
        // =========================
        $order = Order::find($invoice->order_id);
        
        if (!$order) return;

        // =========================
        // STEP 3: 90 DAYS FROM ORDER
        // =========================
        $days = Carbon::parse($order->created_at)
            ->diffInDays(Carbon::parse($invoice->updated_at));
            
        
        if ($days > 90) {
            return;
        }

        // =========================
        // STEP 4: RULE MATCH
        // =========================
        $amount = $invoice->net_price;

        $orderCreatedDate = Carbon::parse($order->created_at)->toDateString();  // ← order date

        
        $rule = LoyaltyRule::where('status', 1)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->first();

        if (!$rule) return;

        // =========================
        // STEP 5: LOUNGE
        // =========================
        if ($rule->reward_type == 'lounge') {
            $expiryDate = Carbon::now()
                            ->addDays($rule->lounge_expiry_days)
                            ->toDateString();

            $loungeBefore = $user->lounge_visits_total - $user->lounge_visits_used;

            $added = $rule->lounge_visits;

            $loungeAfter = $loungeBefore + $added;

            $user->increment('lounge_visits_total', $added);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'points' => 0,
                'lounge_visits' => $added,
                
                  // lounge ledger
                'lounge_before' => $loungeBefore,

                'lounge_after' => $loungeAfter,

                'lounge_used' => 0,

                'source' => 'Full_payment',
                'channel' => 'lounge',
                'reference_id' => $invoice->id,
                'expiry_date'   => $expiryDate
            ]);
        }
        
       

        // =========================
        // STEP 6: POINTS
        // =========================
        if ($rule->reward_type == 'points') {

           $points = $rule->points_type == 'percentage'
                ? round(($amount * $rule->points_value) / 100, 2)
                : round($rule->points_value, 2);

             $expiryDate = Carbon::now()
                            ->addDays($rule->points_expiry_days)
                            ->toDateString();

            $beforePoints = $user->total_points;

            $afterPoints = $beforePoints + $points;

            $user->increment('total_points', $points);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'points' => $points,
                // Point ledger
                'balance_before' => $beforePoints,
                'balance_after' => $afterPoints,
                
                'source' => 'Full_payment',
                'channel' => 'points',
                'reference_id' => $invoice->id,
                'expiry_date'   => $expiryDate
            ]);

        }
        
    }
}