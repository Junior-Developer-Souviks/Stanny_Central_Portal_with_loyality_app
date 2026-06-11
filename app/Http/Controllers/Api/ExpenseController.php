<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Ledger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class ExpenseController extends Controller
{
    public function expenseList(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        // Get all valid payment IDs from Journal
         // Get logged-in user
       $user = Auth::guard('api')->user();

        // Fetch expenses
       $expenses = Payment::where('payment_for', 'debit')
            ->where('created_by', $user->id)
            ->where(function ($q) use ($user) {
                    $q->where('stuff_id', $user->id)
                      ->orWhere('customer_id', $user->id)
                      ->orWhere('supplier_id', $user->id);
                })
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                $query->whereBetween('payment_date', [
                    $request->start_date,
                    $request->end_date
                ]);
            })
           
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($expense) {
        
                // Determine Expense At like Blade
                $expenseAt = '';
                if ($expense->stuff_id) {
                    $staff = DB::table('users')->where('id', $expense->stuff_id)->first();
                    if ($staff) $expenseAt = 'Staff Name: ' . $staff->name;
                } elseif ($expense->customer_id) {
                    $customer = DB::table('users')->where('id', $expense->customer_id)->first();
                    if ($customer) $expenseAt = 'Customer Name: ' . $customer->name;
                } elseif ($expense->supplier_id) {
                    $supplier = DB::table('suppliers')->where('id', $expense->supplier_id)->first();
                    if ($supplier) $expenseAt = 'Supplier Name: ' . $supplier->name;
                }
        
                // Expense Type
                $expenseType = $expense->expense_id ? DB::table('expences')->where('id', $expense->expense_id)->first() : null;
                $expenseTitle = $expenseType ? $expenseType->title : null;
                
                // Created/Updated By
                $creator = $expense->created_by ? DB::table('users')->where('id', $expense->created_by)->first() : null;
                $updater = $expense->updated_by ? DB::table('users')->where('id', $expense->updated_by)->first() : null;
        
                return [
                    'expense_date'   => $expense->payment_date ? Carbon::parse($expense->payment_date)->format('d/m/Y') : null,
                    'transaction_id' => $expense->voucher_no,
                    'amount'         => number_format((float)$expense->amount, 2),
                    'expense_at'     => $expenseAt,
                    'payment_mode'   => $expense->payment_mode,
                    'created_by'     => $creator ? $creator->name : null,
                    'created_at'     => $expense->created_at ? Carbon::parse($expense->created_at)->format('d/m/Y h:i A') : null,
                    'created_from'   => $expense->created_from ?? null,
                    'expense'        => $expenseTitle,
                ];
            });

        return response()->json([
            'status'  => true,
            'message' => 'Expense list fetched successfully',
            'data'    => $expenses
        ]);
    }
    
    public function types(Request $request)
    {
        $query = DB::table('expences')
            ->select(
                'id as expense_id',
                'title',
                'for_customer',
                'for_staff',
                'for_supplier',
                'for_credit',
                'for_debit'
            )
            ->where('status', 1);
    
        // Apply payment filter ONLY if provided
        if ($request->filled('payment_for')) {
            if ($request->payment_for == 'credit') {
                $query->where('for_credit', 1);
            } elseif ($request->payment_for == 'debit') {
                $query->where('for_debit', 1);
            }
        }
    
        // Apply expense filter ONLY if provided
        if ($request->filled('expense_for')) {
            if ($request->expense_for == 'customer') {
                $query->where('for_customer', 1);
            } elseif ($request->expense_for == 'staff') {
                $query->where('for_staff', 1);
            } elseif ($request->expense_for == 'supplier') {
                $query->where('for_supplier', 1);
            } elseif ($request->expense_for == 'partner') {
                $query->where('for_partner', 1);
            }
        }
    
        $data = $query->orderBy('title', 'asc')->get();
    
        return response()->json([
            'error' => false,
            'message' => 'Expense List',
            'data' => ['types' => $data]
        ], 200);
    }
    
    public function addExpense(Request $request)
    {
        // dd($request->all());
        $voucher_no = 'EXPENSE' . time();
        $payment_date = date('Y-m-d');
    
        $user = Auth::guard('sanctum')->user();
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'payment_mode' => 'required|in:cheque,neft,cash',
            'expense_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'expense_id' => ['required',
                                Rule::exists('expences', 'id')
                                    ->where(function ($query) use ($user) {
                                        $query->where('status', 1);
                            
                                        if ($user->user_type == 0) {
                                            $query->where('for_staff', 1);
                                        } elseif ($user->user_type == 1) {
                                            $query->where('for_customer', 1);
                                        }
                                    })],
            'bank_name' => 'required_unless:payment_mode,cash',
            'chq_utr_no' => 'required_unless:payment_mode,cash',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
    
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
    
        DB::beginTransaction();
    
        try {
            
            $expenseProof = null;
            if($request->hasFile('expense_proof')){
                $expenseProof  = Helper::handleFileUpload($request->file('expense_proof'),'expense-proof');
            }
         
    
            $paymentData = [
                'expense_id'   => $request->expense_id,
                'payment_for'  => 'debit',
                'voucher_no'   => $voucher_no,
                'payment_date' => $payment_date,
                'payment_mode' => $request->payment_mode,
                'payment_in'   => ($request->payment_mode != 'cash') ? 'bank' : 'Cash',
                'bank_cash'    => ($request->payment_mode == 'cash') ? 'Cash' : 'bank',
                'amount'       => $request->amount,
                'bank_name'    => $request->bank_name,
                'chq_utr_no'   => $request->chq_utr_no,
                'narration'    => $request->narration,
                'expense_proof'    => $expenseProof,
                'created_by'   => $user->id,
                'created_from' => 'app',
                'stuff_id'     => $user->id, // if user is staff
                'is_ledger_added' => 0,
                'is_approved' => 0,
                'created_at'   => now()
            ];
            
            // dd($paymentData);
    
            $payment = Payment::create($paymentData);
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Expense added successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'voucher_no' => $voucher_no
                ]
            ], 201);
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}