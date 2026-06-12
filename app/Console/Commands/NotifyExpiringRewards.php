<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WalletTransaction;
use App\Services\PushNotificationService;
use Carbon\Carbon;

class NotifyExpiringRewards extends Command
{
    protected $signature   = 'loyalty:notify-expiry';
    protected $description = 'Send push notifications for rewards expiring in 3 days';

    public function handle(PushNotificationService $push)
    {
        // Notify 3 days before expiry
        $targetDate = Carbon::today()->addDays(3)->toDateString();

        $expiring = WalletTransaction::with('user')
            ->where('type', 'credit')
            ->where('is_expired', 0)
            ->whereDate('expiry_date', $targetDate)
            ->get();

        foreach ($expiring as $txn) {
            $user = $txn->user;

            if (!$user || empty($user->fcm_token)) continue;

            // Points expiry notification
            if ($txn->channel === 'points' && $txn->points > 0) {
                $push->send(
                    $user->fcm_token,
                    '⚠️ Points Expiring Soon!',
                    "Your {$txn->points} points will expire on " .
                        Carbon::parse($txn->expiry_date)->format('d M Y') . ". Use them before they expire!",
                    [
                        'type'       => 'points_expiry',
                        'txn_id'     => $txn->id,
                        'points'     => $txn->points,
                        'expiry_date'=> $txn->expiry_date,
                    ]
                );
            }

            // Lounge expiry notification
            if ($txn->channel === 'lounge' && $txn->lounge_visits > 0) {
                $push->send(
                    $user->fcm_token,
                    '✈️ Lounge Visits Expiring Soon!',
                    "Your {$txn->lounge_visits} lounge visit(s) will expire on " .
                        Carbon::parse($txn->expiry_date)->format('d M Y') . ". Book your lounge now!",
                    [
                        'type'        => 'lounge_expiry',
                        'txn_id'      => $txn->id,
                        'lounge_visits'=> $txn->lounge_visits,
                        'expiry_date' => $txn->expiry_date,
                    ]
                );
            }
        }

        $this->info("Notified {$expiring->count()} expiring transactions.");
    }
}