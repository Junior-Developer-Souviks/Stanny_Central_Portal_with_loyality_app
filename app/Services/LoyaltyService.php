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
        
        $rule = LoyaltyRule::where('status', 1)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->first();

        if (!$rule) return;

        // =========================
        // STEP 5: LOUNGE
        // =========================
        if ($rule->reward_type == 'lounge') {
            $user->increment('lounge_visits_total', $rule->lounge_visits);
            
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'points' => 0,
                'lounge_visits' => $rule->lounge_visits,
                'source' => 'Full_payment',
                'channel' => 'lounge',
                'reference_id' => $invoice->id
            ]);
        }
        
       

        // =========================
        // STEP 6: POINTS
        // =========================
        if ($rule->reward_type == 'points') {

            $points = ($rule->points_type == 'percentage')
                ? ($amount * $rule->points_value) / 100
                : $rule->points_value;

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'points' => $points,
                'source' => 'Full_payment',
                'channel' => 'purchase',
                'reference_id' => $invoice->id
            ]);

            $user->increment('total_points', $points);
        }
        
    }
}