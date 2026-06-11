<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\{
    PaymentCollection,
    Invoice,
    InvoicePayment,
    Journal,
    Ledger,
    Payment,
    PaymentRevoke,
    User,
    DayCashEntry,
    Branch
};  

class CashBookController extends Controller
{
    public function cashbook(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $startDate = Carbon::parse($request->start_date ?? now()->toDateString())->startOfDay();
        $endDate   = Carbon::parse($request->end_date ?? now()->toDateString())->endOfDay();
        // $selectedStaffId = $user->id;
        // $staffBranch     = $request->branch_id;

        $firstCollectionDate = PaymentCollection::where('is_approve', 1)
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->value('created_at');

       

        $pastCollections = PaymentCollection::where('is_approve', 1)
            ->whereDate('cheque_date', '<', $startDate)
            ->where('user_id', $user->id)
            ->sum('collection_amount');

        $pastExpenses = Journal::where('is_debit', 1)
            ->whereDate('entry_date', '<', $startDate)
            ->whereHas('payment', fn($p) =>
                $p->where('stuff_id', $user->id)
            )
            ->sum('transaction_amount');

        $openingBalance = $pastCollections - $pastExpenses;

        $baseQuery = PaymentCollection::where('is_approve', 1)
            ->where('user_id', $user->id)
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->where(function ($q) {
                $q->where('payment_type', '!=', 'cheque')
                ->orWhere(fn($sq) =>
                    $sq->where('payment_type', 'cheque')
                        // ->whereNotNull('credit_date')
                );
            });

        $totalCollections = $baseQuery->sum('collection_amount') + $baseQuery->sum('withdrawal_charge');

        $cashCollection = PaymentCollection::where('is_approve', 1)
            ->where('payment_type', 'cash')
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->where('user_id', $user->id)
            ->sum('collection_amount');

        $neftCollection = PaymentCollection::where('is_approve', 1)
            ->where('payment_type', 'neft')
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->where('user_id', $user->id)
            ->sum('collection_amount');

        $digitalCollection = PaymentCollection::where('is_approve', 1)
            ->where('payment_type', 'digital_payment')
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->where('user_id', $user->id)
            ->sum(\DB::raw('collection_amount + withdrawal_charge'));

        $chequeCollection = PaymentCollection::where('is_approve', 1)
            ->where('payment_type', 'cheque')
            // ->whereNotNull('credit_date')
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->where('user_id', $user->id)
            ->sum('collection_amount');

        $totalExpenses = Journal::where('is_debit', 1)
            ->whereNotNull('payment_id')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->whereHas('payment', fn($p) =>
                    $p->where('stuff_id', $user->id)
            )
            ->sum('transaction_amount');

        $collectedFromStaff = DayCashEntry::where('type', 'collected')
            ->whereDate('payment_date', '>=', $firstCollectionDate ?? $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->where('staff_id', $user->id)
            ->sum('amount');

        $givenToStaff = DayCashEntry::where('type', 'given')
            ->whereDate('payment_date', '>=', $firstCollectionDate ?? $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->where('staff_id', $user->id)
            ->sum('amount');

        $wallet = $openingBalance + ($totalCollections - $totalExpenses - $collectedFromStaff + $givenToStaff);

        // $paymentCollections = PaymentCollection::with(['customer', 'user'])
        //     ->where('is_approve', 1)
        //     ->where('user_id', $user->id)
        //     ->where(function ($q) {
        //         $q->where('payment_type', '!=', 'cheque')
        //         ->orWhere(function ($sq) {
        //             $sq->where('payment_type', 'cheque')
        //                 ->whereNotNull('credit_date');
        //         });
        //     })
        //     ->whereBetween('created_at', [$startDate, $endDate])
        //     ->where('collection_amount', '>', 0)
        //     ->orderByDesc('created_at')
        //     ->get();

        // $validPaymentIds = Journal::whereNotNull('payment_id')->pluck('payment_id');
        // $paymentExpenses = Payment::where('payment_for', 'debit')
        //     ->whereIn('id', $validPaymentIds)
        //     ->where('stuff_id', $user->id)
        //     ->whereBetween('created_at', [$startDate, $endDate])
        //     ->orderByDesc('created_at')
        //     ->get();

        return response()->json([
            'status' => true,
            'data' => [
                // 'opening_balance' => $openingBalance,
                'total_collections' => $totalCollections,
                'total_expenses' => $totalExpenses,
                'wallet_balance' => $wallet,
                'total_cash'    => $cashCollection,
                'total_neft'    => $neftCollection,
                'total_cheque'  => $chequeCollection,
                'total_digital' => $digitalCollection,
                // 'collections' => $paymentCollections,
                // 'expenses'    => $paymentExpenses,
            ]
        ]);
    }
}
