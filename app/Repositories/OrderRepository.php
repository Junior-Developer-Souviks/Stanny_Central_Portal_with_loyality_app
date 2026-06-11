<?php

namespace App\Repositories;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackingSlip;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Ledger;
use App\Models\City;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function approveOrder($orderId, $staffId = null)
    {
        try {
            DB::beginTransaction();

            $order = Order::with('items', 'customer')->findOrFail($orderId);
             // Update each item as Approved if needed
                // foreach ($order->items as $item) {
                //     if ($item->status == 'Process' && $item->admin_status != 'Approved') {
                //         $item->admin_status = 'Approved';
                //         $item->save();
                //     }
                // }
                 // ✅ Step 1: Update item admin_status based on creation source
                 $loggedInAdmin = auth()->guard('admin')->user();
            //      foreach ($order->items as $item) {

            //     // Only handle Process items
            //     if ($item->status === 'Process') {

            //         // If admin or super admin is approving
            //         if (in_array($loggedInAdmin->designation, [1, 12])) {
            //             $item->tl_status = 'Approved';
            //             $item->admin_status = 'Approved';
            //             $item->assigned_team = 'production';
                      
            //         }
            //         // If TL/staff approving
            //         else {
            //             $item->tl_status = 'Approved';
            //             $item->admin_status = 'Pending';
            //             // keep assigned_team as is (don’t reset)
            //         }

            //     } else {
            //         // Not in Process (Hold/Cancel)
            //         $item->tl_status = 'Pending';
            //         $item->admin_status = 'Pending';
            //         $item->assigned_team = null;
            //     }

            //     $item->save();
            // }
            foreach ($order->items as $item) {
                // Only handle Process items
                if ($item->status === 'Process') {
                    if (in_array($loggedInAdmin->designation, [1, 12])) {
                        // ✅ Admin auto-approve
                        $item->tl_status = 'Approved';
                        $item->admin_status = 'Approved';
            
                        // ✅ Ensure assigned team is production
                        $item->assigned_team = 'production';
                    } else if ($loggedInAdmin->designation == 4) { // TL
                        $item->tl_status = 'Approved';
                        $item->admin_status = 'Pending';
                        $item->assigned_team = 'production';
                    } else {
                        $item->tl_status = 'Pending';
                        $item->admin_status = 'Pending';
                        $item->assigned_team = null;
                    }
                } else {
                    // For Hold / Cancel / other statuses
                    $item->tl_status = 'Pending';
                    $item->admin_status = 'Pending';
                    $item->assigned_team = null;
                }
            
                $item->save();
            }


                // Determine order status based on items
                $totalItems = $order->items->count();
                $approvedItems = $order->items->where('admin_status', 'Approved')->count();
                $holdItems = $order->items->where('status', 'Hold')->count();
                // dd($totalItems,$approvedItems,$holdItems);
                if ($approvedItems == $totalItems) {
                    $orderStatus = 'Fully Approved By Admin';
                } elseif ($approvedItems > 0 || $holdItems > 0) {
                    $orderStatus = 'Partial Approved By Admin';
                } else {
                    $orderStatus = 'Approval Pending from TL';
                }
            $order->update([
                'status' => $orderStatus,
                'last_payment_date' => now(),
            ]);

            // Recalculate total
            $subtotal = 0;
            foreach ($order->items as $item) {
                $item->total_price = $item->piece_price * $item->quantity;
                $item->save();
                $subtotal += $item->total_price;
            }

            $airMail = $order->air_mail ?? 0;
            $order->update(['total_amount' => $subtotal + $airMail]);

            // Create packing slip
            $packingSlip = PackingSlip::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'slipno' => $order->order_number,
                'is_disbursed' => 0,
                'created_by' => $staffId,
                'created_at' => now(),
                'disbursed_by' => $staffId,
            ]);

            // Create invoice
            $lastInvoice = Invoice::latest()->first();
            $invoice_no = str_pad(optional($lastInvoice)->id + 1, 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'user_id' => $staffId,
                'packingslip_id' => $packingSlip->id,
                'invoice_no' => $invoice_no,
                'net_price' => $order->total_amount,
                'required_payment_amount' => $order->total_amount,
                'created_by' => $staffId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($order->items as $item) {
                InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'order_item_id' => $item->id,
                    'product_name' => optional($item->product)->name ?? '',
                    'quantity' => $item->quantity,
                    'single_product_price' => $item->piece_price,
                    'total_price' => $item->total_price + ($item->air_mail ?? 0),
                    'is_store_address_outstation' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Ledger::insert([
                'user_type' => 'customer',
                'transaction_id' => $invoice_no,
                'customer_id' => $order->customer_id,
                'transaction_amount' => $order->total_amount,
                'bank_cash' => 'cash',
                'is_credit' => 0,
                'is_debit' => 1,
                'entry_date' => now(),
                'purpose' => 'invoice',
                'purpose_description' => 'Invoice raised for customer order',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order Approval Error: ' . $e->getMessage());
            throw $e;
        }
    }
}