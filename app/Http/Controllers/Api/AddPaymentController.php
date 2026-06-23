<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,Ledger,PaymentCollection,TodoList,Journal,Payment,Invoice,InvoicePayment
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AddPaymentController extends Controller
{
     public function addPaymentReceipt(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $rules = [
            'customer_id'       => 'required|exists:users,id',
            'collection_amount' => 'required|numeric|min:0.01',
            'payment_type'      => 'required|in:cash,cheque,digital_payment,neft',
            'payment_date'      => 'required|date',
            'next_payment_date' => 'nullable|date',
            'deposit_date'      => 'nullable|date',
            'remarks'           => 'required'
        ];

        if ($request->payment_type === 'cheque') {
            $rules['cheque_number'] = 'required|string|max:255';
            $rules['deposit_date']  = 'required|date';
            $rules['cheque_file']   = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
            $rules['bank_name']     = 'required|string|max:255';
        }
        
        if ($request->payment_type === 'cash') {
            $rules['receipt_copy_upload']   = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }
        
        if ($request->payment_type === 'neft') {
            $rules['receipt_copy_upload']   = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        if ($request->payment_type === 'digital_payment') {
            $rules['transaction_no']    = 'required|string|max:255';
            $rules['withdrawal_charge'] = 'required|numeric|min:0';
        }

        Validator::make($request->all(), $rules)->validate();

        DB::beginTransaction();

        try {
            
            $customerId = $request->customer_id;
            $staffId    = $user->id;
            $adminId    = $user->id;

            $voucherNo = 'PAYRECEIPT' . now()->timestamp;

            /** -----------------------------
             *  Upload Cheque File
             * ---------------------------- */
            $chequePhoto = null;
            if ($request->hasFile('cheque_file')) {
                $name = Str::random(12) . '.' . $request->file('cheque_file')->getClientOriginalExtension();
                $path = $request->file('cheque_file')->storeAs('uploads/cheque', $name, 'public');
                $chequePhoto = 'storage/' . $path;
            }
            
            $receipt_copy = null;
            if ($request->hasFile('receipt_copy_upload')) {
                $name = Str::random(12) . '.' . $request->file('receipt_copy_upload')->getClientOriginalExtension();
                $path = $request->file('receipt_copy_upload')->storeAs('uploads/receipt_copy', $name, 'public');
                $receipt_copy = 'storage/' . $path;
            }

            /** -----------------------------
             *  Payment Entry
             * ---------------------------- */
            $paymentId = Payment::insertGetId([
                'payment_for' => 'credit',
                'voucher_no'  => $voucherNo,
                'payment_date'=> $request->payment_date,
                'payment_mode'=> $request->payment_type,
                'payment_in'  => $request->payment_type === 'cash' ? 'cash' : 'bank',
                'bank_cash'   => $request->payment_type === 'cash' ? 'cash' : 'bank',
                'amount'      => $request->collection_amount,
                'chq_utr_no'  => $request->cheque_number ?? null,
                'bank_name'   => $request->bank_name ?? null,
                'customer_id' => $customerId,
                'created_by'  => $adminId,
                'created_at'  => now(),
                'is_ledger_added'  => 0,
                'is_approved'       => 0,
                'narration'    => $request->remarks
            ]);

            /** -----------------------------
             *  Ledger Entry
             * ---------------------------- */
            // Ledger::insert([
            //     'user_type'           => 'customer',
            //     'customer_id'         => $customerId,
            //     'transaction_id'      => $voucherNo,
            //     'transaction_amount'  => $request->collection_amount,
            //     'payment_id'          => $paymentId,
            //     'bank_cash'           => $request->payment_type === 'cash' ? 'cash' : 'bank',
            //     'is_credit'           => 1,
            //     'is_debit'            => 0,
            //     'entry_date'          => $request->payment_date,
            //     'purpose'             => 'payment_receipt',
            //     'purpose_description' => 'customer payment',
            //     'created_at'          => now(),
            // ]);

            /** -----------------------------
             *  Journal Entry
             * ---------------------------- */
            // Journal::insert([
            //     'transaction_amount'  => $request->collection_amount,
            //     'is_credit'           => 1,
            //     'is_debit'            => 0,
            //     'entry_date'          => $request->payment_date,
            //     'payment_id'          => $paymentId,
            //     'bank_cash'           => $request->payment_type === 'cash' ? 'cash' : 'bank',
            //     'purpose'             => 'payment_receipt',
            //     'purpose_description' => 'customer payment',
            //     'purpose_id'          => $voucherNo,
            // ]);

            /** -----------------------------
             *  Payment Collection Entry
             * ---------------------------- */
            $paymentCollectionId = PaymentCollection::insertGetId([
                'customer_id'      => $customerId,
                'user_id'          => $staffId,
                'payment_id'       => $paymentId,
                'collection_amount'=> $request->collection_amount,
                'bank_name'        => $request->bank_name ?? null,
                'cheque_number'    => $request->cheque_number ?? null,
                'cheque_date'      => $request->payment_date,
                'payment_type'     => $request->payment_type,
                'voucher_no'       => $voucherNo,
                'is_ledger_added'  => 0,
                'is_approve'       => 0,
                'created_from'     => 'app',
                'cheque_photo'     => $chequePhoto,
                'receipt_copy_upload' =>$receipt_copy,
                'withdrawal_charge'=> $request->withdrawal_charge ?? null,
                'transaction_no'   => $request->transaction_no ?? null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            /** -----------------------------
             *  Invoice Adjustment
             * ---------------------------- */
            $this->invoicePayments(
                $voucherNo,
                $request->payment_date,
                $request->collection_amount,
                $customerId,
                $paymentCollectionId,
                $staffId
            );

            /** -----------------------------
             *  TODO Entries
             * ---------------------------- */
            if ($request->next_payment_date) {
                TodoList::create([
                    'user_id'     => $staffId,
                    'customer_id' => $customerId,
                    'created_by'  => $adminId,
                    'todo_type'   => 'Payment',
                    'todo_date'   => $request->next_payment_date,
                    'remark'      => 'Next Payment Schedule on ' . $request->next_payment_date,
                ]);
            }

            if ($request->deposit_date) {
                TodoList::create([
                    'user_id'     => $staffId,
                    'customer_id' => $customerId,
                    'created_by'  => $adminId,
                    'todo_type'   => 'Cheque Deposit',
                    'todo_date'   => $request->deposit_date,
                    'remark'      => 'Deposit Date ' . $request->deposit_date,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Payment receipt stored successfully',
                'voucher' => $voucherNo,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage(),
            ], 500);
        }
    }
    private function invoicePayments($voucher_no,$payment_date,$payment_amount,$customer_id,$payment_collection_id,$staff_id){
        $check_invoice_payments = InvoicePayment::where('voucher_no', $voucher_no)->get()->toArray();

        if(empty($check_invoice_payments)){
            $amount_after_settlement = $payment_amount;
            $invoice = Invoice::where('customer_id', $customer_id)->where('is_paid', 0)->orderBy('id','asc')->get();
            $sum_inv_amount = 0;
            foreach($invoice as $inv){
                $invoice_date = date('Y-m-d', strtotime($inv->created_at));
                $invoiceOld = date_diff(
                    date_create($invoice_date),
                    date_create($payment_date)
                )->format('%a');

                $year_val = date('Y', strtotime($payment_date));
                $month_val = date('m', strtotime($payment_date));

                $payment_collection = PaymentCollection::find($payment_collection_id);
                $payment_id = $payment_collection->payment_id;
                $store = User::find($customer_id);

                $amount = $inv->required_payment_amount;
                $sum_inv_amount += $amount;

                if($amount == $payment_amount){
                    // die('Full Covered');
                    Invoice::where('id',$inv->id)->update([
                        'required_payment_amount'=>0,
                        'payment_status' => 2,
                        'is_paid'=>1
                    ]);

                    InvoicePayment::insert([
                        'invoice_id' => $inv->id,
                        'payment_collection_id' => $payment_collection_id,
                        'invoice_no' => $inv->invoice_no,
                        'voucher_no' => $voucher_no,
                        'invoice_amount' => $inv->net_price,
                        'vouchar_amount' => $payment_amount,
                        'paid_amount' => $amount,
                        'rest_amount' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    $amount_after_settlement = 0;

                } else{

                    // die('Not Full Covered');

                    if($amount_after_settlement>$amount && $amount_after_settlement>0){
                        $amount_after_settlement=$amount_after_settlement-$amount;
                        Invoice::where('id',$inv->id)->update([
                            'required_payment_amount'=>0,
                            'payment_status' => 2,
                            'is_paid'=>1
                        ]);
                        InvoicePayment::insert([
                            'invoice_id' => $inv->id,
                            'payment_collection_id' => $payment_collection_id,
                            'invoice_no' => $inv->invoice_no,
                            'voucher_no' => $voucher_no,
                            'invoice_amount' => $inv->net_price,
                            'vouchar_amount' => $payment_amount,
                            'paid_amount' => $amount,
                            'rest_amount' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                    }else if($amount_after_settlement<$amount && $amount_after_settlement>0){
                        $rest_payment_amount = ($amount - $amount_after_settlement);
                        Invoice::where('id',$inv->id)->update([
                            'required_payment_amount'=>$rest_payment_amount,
                            'payment_status' => 1,
                            'is_paid'=>0
                        ]);

                        InvoicePayment::insert([
                            'invoice_id' => $inv->id,
                            'payment_collection_id' => $payment_collection_id,
                            'invoice_no' => $inv->invoice_no,
                            'voucher_no' => $voucher_no,
                            'invoice_amount' => $inv->net_price,
                            'vouchar_amount' => $payment_amount,
                            'paid_amount' => $amount_after_settlement,
                            'rest_amount' => $rest_payment_amount,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $amount_after_settlement = 0;
                    }else if($amount_after_settlement==0){

                    }
                }
            }
        }else{
            $invoice_payment = InvoicePayment::where('voucher_no', $voucher_no)->first();

            $payment_amount = (float) $payment_amount;

            if($invoice_payment->vouchar_amount<$payment_amount){
                $payment_amount = $payment_amount-$invoice_payment->vouchar_amount;
                $amount_after_settlement = $payment_amount;
                $invoice = Invoice::where('customer_id', $customer_id)->where('is_paid', 0)->orderBy('id','asc')->get();
                $sum_inv_amount = 0;
                foreach($invoice as $inv){
                    $invoice_date = date('Y-m-d', strtotime($inv->created_at));
                    $invoiceOld = date_diff(
                        date_create($invoice_date),
                        date_create($payment_date)
                    )->format('%a');

                    $year_val = date('Y', strtotime($payment_date));
                    $month_val = date('m', strtotime($payment_date));

                    $payment_collection = PaymentCollection::find($payment_collection_id);
                    $payment_id = $payment_collection->payment_id;
                    $store = User::find($customer_id);

                    $amount = $inv->required_payment_amount;
                    $sum_inv_amount += $amount;

                    if($amount == $payment_amount){
                        // die('Full Covered');
                        Invoice::where('id',$inv->id)->update([
                            'required_payment_amount'=>0,
                            'payment_status' => 2,
                            'is_paid'=>1
                        ]);

                        InvoicePayment::insert([
                            'invoice_id' => $inv->id,
                            'payment_collection_id' => $payment_collection_id,
                            'invoice_no' => $inv->invoice_no,
                            'voucher_no' => $voucher_no,
                            'invoice_amount' => $inv->net_price,
                            'vouchar_amount' => $payment_amount,
                            'paid_amount' => $amount,
                            'rest_amount' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $amount_after_settlement = 0;

                    } else{

                        // die('Not Full Covered');

                        if($amount_after_settlement>$amount && $amount_after_settlement>0){
                            $amount_after_settlement=$amount_after_settlement-$amount;
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>0,
                                'payment_status' => 2,
                                'is_paid'=>1
                            ]);
                            InvoicePayment::insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $voucher_no,
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount,
                                'rest_amount' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

                        }else if($amount_after_settlement<$amount && $amount_after_settlement>0){
                            $rest_payment_amount = ($amount - $amount_after_settlement);
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>$rest_payment_amount,
                                'payment_status' => 1,
                                'is_paid'=>0
                            ]);

                            InvoicePayment::insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $voucher_no,
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount_after_settlement,
                                'rest_amount' => $rest_payment_amount,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

                            $amount_after_settlement = 0;
                        }else if($amount_after_settlement==0){

                        }
                    }
                }

            }elseif($invoice_payment->vouchar_amount>$payment_amount){

                foreach($check_invoice_payments as $k=>$item){
                    $invoice = Invoice::find($item['invoice_id']);
                    $invoice->required_payment_amount = $invoice->required_payment_amount==0?$item['paid_amount']:($invoice->required_payment_amount+$item['paid_amount']);
                    $invoice->payment_status =1;
                    $invoice->is_paid =0;
                    $invoice->save();
                    // Remove Invoice Payment
                    InvoicePayment::where('id', $item['id'])->delete();
                }

                $amount_after_settlement = $payment_amount;
                $invoice = Invoice::where('customer_id', $customer_id)->where('is_paid', 0)->orderBy('id','asc')->get();
                $sum_inv_amount = 0;
                foreach($invoice as $inv){
                    $invoice_date = date('Y-m-d', strtotime($inv->created_at));
                    $invoiceOld = date_diff(
                        date_create($invoice_date),
                        date_create($payment_date)
                    )->format('%a');

                    $year_val = date('Y', strtotime($payment_date));
                    $month_val = date('m', strtotime($payment_date));

                    $payment_collection = PaymentCollection::find($payment_collection_id);
                    $payment_id = $payment_collection->payment_id;
                    $store = User::find($customer_id);

                    $amount = $inv->required_payment_amount;
                    $sum_inv_amount += $amount;

                    if($amount == $payment_amount){
                        // die('Full Covered');
                        Invoice::where('id',$inv->id)->update([
                            'required_payment_amount'=>0,
                            'payment_status' => 2,
                            'is_paid'=>1
                        ]);

                        InvoicePayment::insert([
                            'invoice_id' => $inv->id,
                            'payment_collection_id' => $payment_collection_id,
                            'invoice_no' => $inv->invoice_no,
                            'voucher_no' => $voucher_no,
                            'invoice_amount' => $inv->net_price,
                            'vouchar_amount' => $payment_amount,
                            'paid_amount' => $amount,
                            'rest_amount' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $amount_after_settlement = 0;

                    } else{

                        // die('Not Full Covered');

                        if($amount_after_settlement>$amount && $amount_after_settlement>0){
                            $amount_after_settlement=$amount_after_settlement-$amount;
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>0,
                                'payment_status' => 2,
                                'is_paid'=>1
                            ]);
                            InvoicePayment::insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $voucher_no,
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount,
                                'rest_amount' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

                        }else if($amount_after_settlement<$amount && $amount_after_settlement>0){
                            $rest_payment_amount = ($amount - $amount_after_settlement);
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>$rest_payment_amount,
                                'payment_status' => 1,
                                'is_paid'=>0
                            ]);

                            InvoicePayment::insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $voucher_no,
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount_after_settlement,
                                'rest_amount' => $rest_payment_amount,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

                            $amount_after_settlement = 0;
                        }else if($amount_after_settlement==0){

                        }
                    }
                }
            }else{

            }

        }
    }
    
    public function paymentCollection(Request $request)
    {
         if (!Auth::guard('api')->check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not logged in'
                ], 401);
            }

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);
        
        $user = Auth::guard('api')->user();
    
        $query = PaymentCollection::with(['customer', 'user'])
            ->where('collection_amount', '>', 0)
            ->where('user_id', $user->id) 
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('cheque_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('cheque_date', '<=', $request->end_date);
            })
            ->orderBy('cheque_date', 'desc');
    
        $collections = $query->get();
    
        $data = $collections->map(function ($item, $key) {
    
            return [
                'id' => $item->id,
                'voucher_no' => $item->voucher_no,
                'payment_date' => $item->cheque_date 
                    ? \Carbon\Carbon::parse($item->cheque_date)->format('d/m/Y') 
                    : null,
                'collected_by' => ($item->user->name ?? '') . ($item->user->surname ?? ''),
                'customer_id' => $item->customer->id ?? null,
                'customer_name' => $item->customer->name ?? null,
                'collection_amount' =>  number_format($item->collection_amount, 2)
                    . ' (' . ($item->payment_type ?? 'Cash') . ')',
                'collected_from' => $item->created_from ?? null,
                'company_name' => $item->customer->company_name ?? null,
                'phone' => $item->customer->phone ?? null,
                'bank' => $item->bank_name ?? null,
                'payment_type' => $item->payment_type ?? null,
                'approval_status' => $item->is_approve ? 'Approved' : 'Not Approved',
            ];
        });
    
        return response()->json([
            'status' => true,
            'count'  => $data->count(),
            'data'   => $data
        ]);
    }

}
