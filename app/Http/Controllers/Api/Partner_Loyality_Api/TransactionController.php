<?php

namespace App\Http\Controllers\Api\Partner_Loyality_Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
        public function transactionHistory(Request $request, $qr_code)
    {
        // STEP 1: Get Logged-In Staff (the partner)
        $staff = auth()->user();
    
        /*
        DESIGNATION MAPPING
        14 = AIRPORT PARTNER
        15 = GROCERY PARTNER
        16 = STORE SALES
        */
        $allowedDesignations = [14, 15, 16];
    
        if (!in_array($staff->designation, $allowedDesignations)) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    
        // STEP 2: Find Customer by QR Code
        $customer = User::where('qr_code', $qr_code)
            ->where('user_type', 1)
            ->first();
    
        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer not found'
            ], 404);
        }
    
        // STEP 3: Build Query
        // reference_id = staff->id ensures each partner
        // sees ONLY the transactions THEY processed
        $query = WalletTransaction::where('user_id', $customer->id)
            ->where('reference_id', $staff->id)   // ← KEY FILTER
            ->orderBy('created_at', 'desc');
    
        // STEP 4: Date Filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
    
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
    
        // STEP 5: Paginate
        $transactions = $query->paginate(10);
    
        // STEP 6: Format
        $formatted = $transactions->map(function ($txn) use ($customer) {
            return [
                'id'            => $txn->id,
                'customer_name' => $customer->name,
                'card_number'   => $customer->card_number,
                'type'          => $txn->type,
                'source'        => $txn->source,
                'channel'       => $txn->channel,
                'points'        => $txn->points,
                'lounge_visits' => $txn->lounge_visits,
                'expiry_date'   => $txn->expiry_date,
                'date'          => $txn->created_at
                                       ? $txn->created_at->format('d M, Y')
                                       : null,
            ];
        });
    
        // STEP 7: Return
        return response()->json([
            'status' => true,
            'data'   => [
                'staff_name'    => $staff->name,
                'customer_name' => $customer->name,
                'card_number'   => $customer->card_number,
                'transactions'  => $formatted,
                'pagination'    => [
                    'current_page' => $transactions->currentPage(),
                    'last_page'    => $transactions->lastPage(),
                    'per_page'     => $transactions->perPage(),
                    'total'        => $transactions->total(),
                ]
            ]
        ]);
    }
}
