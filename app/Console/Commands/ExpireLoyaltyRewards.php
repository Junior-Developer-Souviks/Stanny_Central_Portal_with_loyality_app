<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WalletTransaction;
use App\Models\User;
use Carbon\Carbon;

class ExpireLoyaltyRewards extends Command
{
    protected $signature   = 'loyalty:expire';
    protected $description = 'Expire points and lounge visits past their expiry date';

    public function handle()
    {
        $expired = WalletTransaction::where('type', 'credit')
            ->where('is_expired', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', Carbon::today())
            ->get();

        foreach ($expired as $txn) {
            $user = User::find($txn->user_id);
            if (!$user) continue;

            if ($txn->channel === 'points' && $txn->points > 0) {
                $deduct = min($txn->points, $user->total_points);
                $user->decrement('total_points', $deduct);

                WalletTransaction::create([
                    'user_id'      => $user->id,
                    'type'         => 'debit',
                    'points'       => $deduct,
                    'source'       => 'expiry',
                    'channel'      => 'points',
                    'reference_id' => $txn->id,
                    'expiry_date'  => null,
                ]);
            }

            if ($txn->channel === 'lounge' && $txn->lounge_visits > 0) {
                $deduct = min($txn->lounge_visits, $user->lounge_visits_total);
                $user->decrement('lounge_visits_total', $deduct);

                WalletTransaction::create([
                    'user_id'       => $user->id,
                    'type'          => 'debit',
                    'points'        => 0,
                    'lounge_visits' => $deduct,
                    'source'        => 'expiry',
                    'channel'       => 'lounge',
                    'reference_id'  => $txn->id,
                    'expiry_date'   => null,
                ]);
            }

            // Mark original as expired
            $txn->update(['is_expired' => 1]);
        }

        $this->info("Expired {$expired->count()} transactions.");
    }
}