<?php
// app/Repositories/UserRepository.php
namespace App\Repositories;

use App\Interfaces\AccountingRepositoryInterface;
use App\Models\Payment;
use App\Models\Ledger;
use App\Models\Journal;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentCollection;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class AccountingRepository implements AccountingRepositoryInterface
{
    public function StorePaymentReceipt(array $data)
    {

    $admin_id = Auth::guard('admin')->user()->id;
    if(empty($data['payment_collection_id'])){
        $check_store_unpaid_invoices = Invoice::where('customer_id', $data['customer_id'])->where('is_paid',
        0)->get()->toarray();

        $check_outstanding_amount =
        Invoice::where('customer_id',$data['customer_id'])->where('is_paid',0)->sum('required_payment_amount');

        $check_not_receipt_payment_amount =
        PaymentCollection::where('customer_id',$data['customer_id'])->where('is_ledger_added',0)->first();

        $paymentData = array(
            'payment_for' => 'credit',
            'voucher_no' => $data['voucher_no'],
            'payment_date' => $data['payment_date'],
            'payment_mode' => $data['payment_mode'],
            'payment_in' => ($data['payment_mode'] != 'cash') ? 'bank' : 'cash' ,
            'bank_cash' => ($data['payment_mode'] == 'cash') ? 'cash' : 'bank',
            'amount' => $data['amount'],
            'chq_utr_no' => !empty($data['chq_utr_no'])?$data['chq_utr_no']:'',
            'bank_name' => !empty($data['bank_name'])?$data['bank_name']:'',
            'created_by' => Auth::guard('admin')->user()->id
        );

        // Receipt for Customer
        if($data['receipt_for']=="Customer"){
            $user_type = "customer";
            $paymentStore = array('customer_id' => $data['customer_id']);
            $paymentData = array_merge($paymentData,$paymentStore);
        }

        $payment_id = Payment::insertGetId($paymentData);

        $is_credit = 1;

        $is_debit = 0;

        $ledgerData = array(
        'user_type' => $user_type,
        'transaction_id' => $data['voucher_no'],
        'transaction_amount' => $data['amount'],
        'payment_id' => $payment_id,
        'bank_cash' => ($data['payment_mode'] == 'cash') ? 'cash' : 'bank',
        'is_credit' => $is_credit,
        'is_debit' => $is_debit,
        'entry_date' => $data['payment_date'],
        'purpose' => 'payment_receipt',
        'purpose_description' => 'customer payment',
        'created_at' => date('Y-m-d H:i:s'),
        );

        // Receipt for Customer
        if($data['receipt_for']=="Customer"){
            $ledgerStore = array('customer_id' => $data['customer_id']);
            $ledgerData = array_merge($ledgerData,$ledgerStore);
        }

        Ledger::insert($ledgerData);

        /* Entry in journal */
        Journal::insert([
        'transaction_amount' => $data['amount'] ?? null,
        'is_credit' => $is_credit,
        'is_debit' => $is_debit,
        'entry_date' => $data['payment_date'],
        'payment_id' => $payment_id,
        'bank_cash' => ($data['payment_mode'] == 'cash') ? 'cash' : 'bank',
        'purpose' => 'payment_receipt',
        'purpose_description' => 'customer payment',
        'purpose_id' => $data['voucher_no']
        ]);
    }
        /* Payment Collection Entry */

        if(empty($data['payment_collection_id'])){
            $arrPaymentCollection = array(
            'customer_id' => $data['customer_id'],
            'user_id' => $data['staff_id'],
            'admin_id' => $admin_id,
            'payment_id' => $payment_id,
            'collection_amount' => $data['amount'],
            'bank_name' => !empty($data['bank_name'])?$data['bank_name']:'',
            'cheque_number' => !empty($data['chq_utr_no'])?$data['chq_utr_no']:'',
            'cheque_date' => $data['payment_date'],
            'payment_type' => $data['payment_mode'],
            'voucher_no' => $data['voucher_no'],
            'is_ledger_added' => 1,
            'is_approve' => 1,
            'created_from' => 'web',
            'credit_date' => !empty($data['credit_date'])?$data['credit_date']:null,
            'cheque_photo' => $data['cheque_photo'] ?? null,
            'withdrawal_charge' => $data['withdrawal_charge'] ?? null,
            'transaction_no' => $data['transaction_no'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
            );
            $payment_collection_id = PaymentCollection::insertGetId($arrPaymentCollection);
            
            $orders = Order::where('customer_id', $data['customer_id'])->get();

            foreach ($orders as $order) {
            
                $paid = $order->paid_amount + $data['amount'];
            
                $order->update([
                    'paid_amount' => $paid,
                    'last_payment_date' => $data['payment_date'],
                ]);
            }

            $this->invoicePayments($data['voucher_no'],$data['payment_date'],$data['amount'],$data['customer_id'],$payment_collection_id,$data['staff_id']);

        }else{
            // Update in Payment
            $payment = Payment::find($data['payment_id']);
            $payment->amount = $data['amount'];
            $payment->updated_by = $admin_id;
            $payment->updated_at = date('Y-m-d H:i:s');
            $payment->save();

            // Update in Ledger
            Ledger::where('payment_id', $data['payment_id'])->update([
                'transaction_amount' =>$data['amount'],
                'updated_at' =>date('Y-m-d H:i:s'),
            ]);

            // Update in Journal
            Journal::where('payment_id', $data['payment_id'])->update([
                'transaction_amount' =>$data['amount'],
                'updated_at' =>date('Y-m-d H:i:s'),
            ]);

            # From App End
            PaymentCollection::where('id',$data['payment_collection_id'])->update([
                'payment_id' => $data['payment_id'],
                'collection_amount' => $data['amount'],
                'is_ledger_added' => 1,
                'voucher_no' => $data['voucher_no'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->invoicePayments($data['voucher_no'],$data['payment_date'],$data['amount'],$data['customer_id'],$data['payment_collection_id'],$data['staff_id']);
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
                    Invoice::where('id',$inv->id)->update([
                        'required_payment_amount'=>0,
                        'payment_status' => 2,
                        'is_paid'=>1
                    ]);
                    
                    $inv->refresh();

                    
                    if ($inv->payment_status == 2) {
                            app(\App\Services\LoyaltyService::class)
                                ->processInvoice($inv);
                        }

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

    public function StoreOpeningBalance(array $data){

        $is_credit = 0;
        $is_debit = 0;

        if($data['credit_debit'] == 'credit'){
            $is_credit = 1;
        }
        if($data['credit_debit'] == 'debit'){
            $is_debit = 1;
        }

        $payment_in = $data['payment_type'];
        if(!empty($data['customer_id'])){
            # Check exist opening balance for customer with payment_type
            $existOB = Ledger::with('payment')
                              ->where('customer_id',$data['customer_id'])
                              ->where('user_type','customer')
                              ->where('purpose','opening_balance')
                              ->get()
                              ->toArray();
                            //   dd($existOB[0]['entry_date']);
            if(!empty($existOB)){
                 # restrict previous date of existing OB date
                 if($data['date'] < $existOB[0]['entry_date']){
                    $err_msg_date = "Previous Date (".date('d/m/Y',strtotime($existOB[0]['entry_date'])).") of your existing opening balance is not allowed ";
                    return ['status' => false, 'message' => $err_msg_date];
                 }
                   # check bank and cash entry exists
                foreach ($existOB as $ob) {
                    if(in_array($data['payment_type'],array("bank","bank_cash")) && $ob['bank_cash'] == 'bank'){
                        $err_msg_bank = "Already bank entry exists";
                        return ['status' => false, 'message' => $err_msg_bank];
                    }
                    if(in_array($data['payment_type'],array("cash","bank_cash")) && $ob['bank_cash'] == 'cash'){
                        $err_msg_bank = "Already cash entry exists";
                        return ['status' => false, 'message' => $err_msg_bank];
                    }
                }
            }

        }
         /* For customer opening balance */
         $customer_id = $data['customer_id'];
         $user_type = "customer";

         # add OB at the top of the existing transaction of the day
         if($data['payment_type'] == 'bank_cash'){
            if(isset($data['bank_amount'])){
                /* Entry in payment */
                $payment_id = Payment::insertGetId([
                    'customer_id' => $customer_id,
                    'payment_for'=> $data['credit_debit'],
                    'voucher_no'  => $data['voucher_no'],
                    'payment_date'=> $data['date'],
                    'payment_in'=> $data['payment_type'],
                    'bank_cash'   => 'bank',
                    'payment_mode'=> $data['payment_mode'],
                    'amount'      => $data['bank_amount'],
                    'chq_utr_no'  => $data['transaction_no'],
                    'bank_name'   => $data['bank_name'],
                    'narration'   => $data['narration'],
                    'created_by' => Auth::guard('admin')->user()->id,
                    'created_at'=>date('Y-m-d H:i:s')
                ]);
                /* Entry in ledger */
                Ledger::insert([
                    'user_type'=> $user_type,
                    'customer_id'=> $customer_id,
                    'transaction_id'=> $data['voucher_no'],
                    'transaction_amount'=> $data['bank_amount'],
                    'payment_id'=> $payment_id,
                    'bank_cash'=> 'bank',
                    'is_credit'=> $is_credit,
                    'is_debit'=> $is_debit,
                    'entry_date'=> $data['date'],
                    'purpose'=> 'opening_balance',
                    'purpose_description'=> $user_type.' opening balance',
                    'created_at'=> date('Y-m-d H:i:s')
                ]);
                /* Entry in journal */
                Journal::insert([
                    'transaction_amount'=> $data['bank_amount'],
                    'is_credit'=> $is_credit,
                    'is_debit'=> $is_debit,
                    'entry_date'=> $data['date'],
                    'payment_id'=> $payment_id,
                    'bank_cash'=> 'bank',
                    'purpose'=> 'opening_balance',
                    'purpose_description'=> $user_type.' opening balance',
                    'purpose_id'=> $data['voucher_no'],
                    'created_at'=> date('Y-m-d H:i:s')
                ]);
            }
            if(isset($data['cash_amount'])){
                /* Entry in payment */
                $payment_id = Payment::insertGetId([
                    'customer_id'=> $customer_id,
                    'payment_for'=> $data['credit_debit'],
                    'voucher_no'=> $data['voucher_no'],
                    'payment_date'=> $data['date'],
                    'payment_in'=> $data['payment_type'],
                    'bank_cash'=> 'cash',
                    'payment_mode'=> $data['payment_mode'],
                    'amount'=> $data['cash_amount'],
                    'chq_utr_no'=> '',
                    'bank_name'=> '',
                    'narration'=> $data['narration'],
                    'created_by'=> Auth::guard('admin')->user()->id,
                    'created_at'=> date('Y-m-d H:i:s')
                ]);
                /* Entry in ledger */
                Ledger::insert([
                    'user_type'=> $user_type,
                    'customer_id'=> $customer_id,
                    'transaction_id'=> $data['voucher_no'],
                    'transaction_amount'=> $data['cash_amount'],
                    'payment_id'=> $payment_id,
                    'bank_cash'=> 'cash',
                    'is_credit'=> $is_credit,
                    'is_debit'=> $is_debit,
                    'entry_date'=> $data['date'],
                    'purpose'=> 'opening_balance',
                    'purpose_description'=> $user_type.' opening balance',
                    'created_at'=> date('Y-m-d H:i:s')
                ]);
                /* Entry in journal */
                Journal::insert([
                    'transaction_amount'=> $data['cash_amount'],
                    'is_credit'=> $is_credit,
                    'is_debit'=> $is_debit,
                    'entry_date'=> $data['date'],
                    'payment_id'=> $payment_id,
                    'bank_cash'=> 'cash',
                    'purpose'=> 'opening_balance',
                    'purpose_description'=> $user_type.' opening balance',
                    'purpose_id'=> $data['voucher_no'],
                    'created_at'=> date('Y-m-d H:i:s')
                ]);
            }
            $err_msg_bank = "Opening balance for customer added successfully";
            return ['status' => true, 'message' => $err_msg_bank];
         }else{
             /* Entry in payment */
            $payment_id = Payment::insertGetId([
                'customer_id'=> $customer_id,
                'payment_for'=> $data['credit_debit'],
                'voucher_no'=> $data['voucher_no'],
                'payment_date'=> $data['date'],
                'payment_in'=> $data['payment_type'],
                'bank_cash'=> ($data['payment_type'] != 'bank') ? 'cash' : $data['payment_type'],
                'payment_mode'=> $data['payment_mode'],
                'amount'=> $data['amount'],
                'chq_utr_no'=> $data['transaction_no'],
                'bank_name'=> $data['bank_name'],
                'narration'=> $data['narration'],
                'created_by'=> Auth::guard('admin')->user()->id,
                'created_at'=> date('Y-m-d H:i:s')
            ]);

                /* Entry in ledger */
            $ledger= Ledger::insert([
                    'user_type'=> $user_type,
                    'customer_id'=> $customer_id,
                    'transaction_id'=> $data['voucher_no'],
                    'transaction_amount'=> $data['amount'],
                    'payment_id'=> $payment_id,
                    'bank_cash'=> ($data['payment_type'] != 'bank') ? 'cash' : $data['payment_type'],
                    'is_credit'=> $is_credit,
                    'is_debit'=> $is_debit,
                    'entry_date'=> $data['date'],
                    'purpose'=> 'opening_balance',
                    'purpose_description'=> $user_type.' opening balance',
                    'created_at'=> date('Y-m-d H:i:s')
                ]);

                /* Entry in journal */
            $journal =Journal::insert([
                'transaction_amount'=> $data['amount'],
                'is_credit'=> $is_credit,
                'is_debit'=> $is_debit,
                'entry_date'=> $data['date'],
                'payment_id'=> $payment_id,
                'bank_cash'=> ($data['payment_type'] != 'bank') ? 'cash' : $data['payment_type'],
                'purpose'=> 'opening_balance',
                'purpose_description'=> $user_type.' opening balance',
                'purpose_id'=> $data['voucher_no'],
                'created_at'=> date('Y-m-d H:i:s')
            ]);
            $err_msg_bank = "Opening balance for customer added successfully";
            return ['status' => true, 'message' => $err_msg_bank];
         }

    }
}
