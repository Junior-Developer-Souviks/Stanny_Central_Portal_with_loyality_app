<?php

namespace App\Http\Controllers\Api\Partner_Loyality_Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransactionController extends Controller
{
        public function transactionHistory(Request $request)
    {
        // Logged in partner staff
        $staff = $request->user();

        /*
            designation 15 = Airport       → show lounge transactions
            designation 16 = Grocery       → show point transactions
            designation 17 = Store         → show point transactions
            designation 02 = Sales Person  → show point transactions
        */

       $validator = Validator::make($request->all(), [
            'from' => 'nullable|date_format:Y-m-d',
            'to'   => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        if ($request->filled('from') && $request->filled('to')) {

            $from = Carbon::createFromFormat('Y-m-d', $request->from);
            $to   = Carbon::createFromFormat('Y-m-d', $request->to);

            if ($to->lessThan($from)) {
                return response()->json([
                    'status' => false,
                    'message' => 'To date must be equal or after from date'
                ], 422);
            }
        }
        $query = WalletTransaction::with('customer')
            ->where('type', 'debit')
            ->where('redeemed_by', $staff->id); // only THIS staff's transactions

        // Airport staff sees lounge redemptions only
        if ($staff->designation == 15) {
            $query->where('source', 'lounge_redemption')
                  ->where('channel', 'airport');
        }

        // Grocery staff sees point redemptions only
        if ($staff->designation == 16) {
            $query->where('source', 'point_redemption')
                  ->where('channel', 'grocery');
        }

        // Store staff
        if ($staff->designation == 17) {
            $query->where('source', 'point_redemption')
                  ->where('channel', 'store_sales');
        }

        // 
        if ($staff->designation == 2) {
            $query->where('source', 'point_redemption')
                  ->where('channel', 'sales_person');
        }

        // Date filters
        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

       $transactions = $query->orderBy('id', 'desc')
                      ->get();

        return response()->json([
            'status' => true,
            'data'   => $transactions->map(function ($item) {
                return [
                    'customer_name' => $item->customer->name ?? '—',
                    'card_number'   => $item->customer->card_number ?? '—',

                    // Points or Lounge
                    'points_deducted' => $item->points ?? 0,
                    'number_of_passengers' => $item->lounge_visits ?? 0,

                    'channel' => $item->channel,
                    'source'  => $item->source,

                    'date' => optional($item->created_at)->format('d M Y'),
                    'time'  => optional($item->created_at)->format('h:i A'),
                ];
            }),
        ]);
    }
}
