<?php

namespace App\Http\Livewire\Order;
use App\Models\Order;
use App\Models\OrderItem;

use \App\Models\Product;
use \App\Models\Invoice;
use App\Models\Delivery;
use App\Models\Measurement;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Carbon\Carbon;

class OrderView extends Component
{
    public $latestOrders = [];
    public $order;
    protected $listeners = ['deliveredToCustomerPartial','openDeliveryModal','markReceivedConfirmed'];
    public $Id, $orderId, $status, $remarks;
    protected $rules = [
        'status' => 'required',
        'remarks' => 'required|string|min:3',
    ];
    public function mount($id){
        $this->orderId = $id;
        $this->order = Order::with(['items','files'])->findOrFail($this->orderId);
        // dd($this->order);
        $invoicePayment = Invoice::where('order_id', $this->order->id)->orderBy('id','desc')->first();
        if($invoicePayment){
            $this->order->total_amount = $invoicePayment->net_price;
            $this->order->paid_amount = $invoicePayment->net_price - $invoicePayment->required_payment_amount;
            $this->order->remaining_amount = $invoicePayment->required_payment_amount;
        }
         // Fetch the latest 5 orders for the user (customer)
         $this->latestOrders = Order::where('customer_id',$this->order->customer_id)
                                     ->latest()
                                     ->where('id', '!=', $this->order->id)
                                     ->take(5)
                                     ->get();
    }

    public function render()
    {
         // Fetch the order and its related items
        $order = Order::with([
            'items.catalogue',
            'items.deliveries' => function ($q) {
                $q->with('user:id,name');
            },
            'items.voice_remark',
            'items.catlogue_image'
        ])->findOrFail($this->orderId);
        
         $orderItems = $order->items->map(function ($item) use($order) {
            $product = Product::find($item->product_id);
            $delivery = $item->deliveries->first();
             // Decide extra measurement type
             $extra = \App\Helpers\Helper::ExtraRequiredMeasurement($item->product_name);
              //  Build item-specific measurements
            $measurements = Measurement::where('product_id', $item->product_id)
                ->orderBy('position','ASC')
                ->get()
                ->map(function ($measurement) use ($item) {
                    $selected = $item->measurements->firstWhere('measurement_name', $measurement->title);
                    return [
                        'measurement_name'          => $measurement->title,
                        'measurement_title_prefix'  => $measurement->short_code,
                        'measurement_value'         => $selected ? $selected->measurement_value : '',
                        'measurement_remarks'         => $selected ? $selected->remarks : '',
                    ];
                });
            return [
                // 'product_name' => $item->product_name ?? $product->name,
                'product_name' => $item->product_name ?? ($product ? $product->name : ''),
                'measurements' => $measurements,
                'collection_id' => $item->collection,
                'collection_title' => $item->collectionType ?  $item->collectionType->title : "",
                'fabrics' => $item->fabric,
                'catalogue' => optional(optional($item->catalogue)->catalogueTitle)->title ?? "",
                'catalogue_id' => $item->catalogue_id,
                'cat_page_number' => $item->cat_page_number,
                'cat_page_item' => $item->cat_page_item,
                'price' => $item->piece_price,
               
                'deliveries' => $delivery ? [
                    'id' => $delivery->id,
                    'delivered_at' => $delivery->delivered_at,
                    'received_at_salesman' => $delivery->received_at_salesman,
                    'delivered_by' => $delivery->delivered_by,
                    'status' => $delivery->status,
                    'remarks' => $delivery->remarks,
                    'fabric_quantity' => $delivery->fabric_quantity,
                    'delivered_quantity' => $delivery->delivered_quantity,
                    'user' => $delivery->user ? ['name' => $delivery->user->name] : ['name' => 'N/A'],
                    'collection_id' => $item->collection,
                ] : null,
                'quantity' => $item->quantity,
                'remarks' => $item->remarks,
                'catlogue_images' => $item->catlogue_image,
                'voice_remarks' => $item->voice_remark,

                'product_image' => $product ? $product->product_image : null,
                'expected_delivery_date' => $item->expected_delivery_date,
                'fittings' => $item->fittings,
                'priority' => $item->priority_level,

                // Extra fields packed here
                'extra_type'           => $extra,
                'mens_hand_stitching'   => $item->mens_hand_stitching,
                'ladies_hand_stitching' => $item->ladies_hand_stitching,
                'shoulder_type'        => $item->shoulder_type,
                'vents'                => $item->vents,
                'vents_required'       => $item->vents_required,
                'vents_count'          => $item->vents_count,
                'fold_cuff_required'   => $item->fold_cuff_required,
                'fold_cuff_size'       => $item->fold_cuff_size,
                'pleats_required'      => $item->pleats_required,
                'pleats_count'         => $item->pleats_count,
                'back_pocket_required' => $item->back_pocket_required,
                'back_pocket_count'    => $item->back_pocket_count,
                'adjustable_belt'      => $item->adjustable_belt,
                'suspender_button'     => $item->suspender_button,
                'trouser_position'     => $item->trouser_position,   
                'client_name_required'     => $item->client_name_required,   
                'client_name_place'     => $item->client_name_place,   
                'client_name_options'     => $item->client_name_options,   
            ];
        });

        return view('livewire.order.order-view',[
            'order' => $order,
            'orderItems' => $orderItems,
            'latestOrders'=>$this->latestOrders,
        ]);
    }

      public function deliveredToCustomerPartial()
    {
        $this->validate();

        if (!$this->Id) {
            throw new \Exception("Order ID is required but received null.");
        }

        // Update the current delivery
        Delivery::where('id', $this->Id)->update([
            'status' => $this->status,
            'remarks' => $this->remarks,
            'customer_delivered_by' => auth()->guard('admin')->user()->id,
        ]);

        // Get all order items for this order
        $orderItems = OrderItem::where('order_id', $this->orderId)->get();
        $totalItems = $orderItems->count();

        // Count of items that have at least one 'Delivered' delivery record
        $deliveredCount = OrderItem::where('order_id', $this->orderId)
            ->whereHas('deliveries', function ($query) {
                $query->where('status', 'Delivered');
            })
            ->count();

        // Decide final order status
        if ($deliveredCount == $totalItems && $totalItems > 0) {
            $newStatus = 'Delivered to Customer';
        } elseif ($deliveredCount > 0) {
            $newStatus = 'Partial Delivered to Customer';
        } else {
            $newStatus = 'Pending';  // fallback
        }

        Order::where('id', $this->orderId)->update(['status' => $newStatus]);

        session()->flash('success', 'Order delivery updated successfully!');
        $this->dispatch('close-delivery-modal');
    }

   
   

    public function openDeliveryModal($Id=null,$orderId=null)
    {
        $this->Id = $Id;
        $this->orderId = $orderId;
    }
    
    public function markReceivedConfirmed($Id=null)
    {
        //\Log::info("Mark As Received By Sales Team Method method triggered with Order ID: " . ($orderId ?? 'NULL'));

        if (!$Id) {
            throw new \Exception("Order ID is required but received null.");
        }
        Delivery::where('id', $Id)
        ->update( [
                'status' =>'Received by Sales Team',
                'received_at_salesman' => now(),
            ]);
        session()->flash('success', 'Delivery has been receive by sales team!');
        return redirect(url()->previous())->with('success', 'Order has been Delivered to Customer successfully!');


    }
 
    
   
    
   public function generatePdf($id)
   {
        $order = Order::with([
            'customer' => function ($q) {
                $q->select('id', 'country_code_phone', 'phone', 'employee_rank','company_name');
            },
            'items.deliveries' => function ($q) {
                $q->with('user:id,name');
            },
            'items.voice_remark',
            'items.catlogue_image',
        ])->findOrFail($id);

    // ─────────────────────────────────────────────
    // PREVIOUS ORDER
    // ─────────────────────────────────────────────

    $last_order = Order::where('customer_id', $order->customer_id)
        ->where('id', '<', $id)
        ->orderBy('id', 'desc')
        ->first();

    // ─────────────────────────────────────────────
    // NEXT ORDER
    // ─────────────────────────────────────────────

    $next_order = Order::where('customer_id', $order->customer_id)
        ->where('id', '>', $id)
        ->orderBy('id', 'asc')
        ->first();

    // ═════════════════════════════════════════════
    // ITEMS STATUS LOGIC
    // ═════════════════════════════════════════════

    $items = [];

    $item_sold = [];

    $item_delivered = [];

    $rest_items = [];

    $net_qty = 0;

    foreach ($order->items as $item) {

        $net_qty += $item->quantity;

        $item_sold[] = $item->quantity . ' ' . $item->product_name;

        $delivered_qty = 0;

        $hold_date = null;

        // ─────────────────────────────────────────
        // DELIVERY STATUS CHECK
        // ─────────────────────────────────────────

        foreach ($item->deliveries as $delivery) {

            switch ($delivery->status) {

                case 'Delivered':

                    preg_match(
                        '/\d+/',
                        $delivery->delivered_quantity,
                        $matches
                    );

                    $delivered_qty += isset($matches[0])
                        ? (int) $matches[0]
                        : 1;

                    break;

                case 'Hold/Pass':

                    if (!empty($delivery->delivery_date)) {

                        $hold_date = Carbon::parse(
                            $delivery->delivery_date
                        )->format('d-m-Y');
                    }

                    break;
            }
        }

        // ─────────────────────────────────────────
        // PREVENT OVER DELIVERY
        // ─────────────────────────────────────────

        $delivered_qty = min(
            $delivered_qty,
            $item->quantity
        );

        $rest_qty = $item->quantity - $delivered_qty;

        // ═════════════════════════════════════════
        // SIMPLE STATUS LOGIC
        // ONLY:
        // PASS
        // HOLD/PASS
        // ═════════════════════════════════════════

        if ($delivered_qty >= $item->quantity) {

            $status = 'Pass';

        } else {

            $status = $hold_date
                ? 'Hold/Pass (' . $hold_date . ')'
                : 'Hold/Pass';
        }

        // ═════════════════════════════════════════
        // ITEMS ARRAY
        // ═════════════════════════════════════════

        $items[] = [

            'name' => $item->quantity . ' ' . $item->product_name,

            'status' => $status,

            'order_date' => Carbon::parse(
                $order->created_at
            )->format('d.m.Y'),

            'delivery_date' => (function() use ($item,$delivered_qty) {
                $delivery = $item->deliveries->where('status', 'Delivered')->last();
                if ($delivered_qty > 0 && $delivery && $delivery->updated_at) {
                    return Carbon::parse($delivery->updated_at)->format('d.m.Y');
                }
                return '';
            })(),
               'received_at_salesman' => (function() use ($item, $delivered_qty) {
                $lastDelivery = $item->deliveries->last();
                if ($delivered_qty > 0 && $lastDelivery && !empty($lastDelivery->received_at_salesman)) {
                    return Carbon::parse($lastDelivery->received_at_salesman)->format('d.m.Y');
                }
                return '';
            })(),
        ];

        // ─────────────────────────────────────────
        // LEGACY ARRAYS
        // ─────────────────────────────────────────

        if ($delivered_qty > 0) {

            $item_delivered[] =
                $delivered_qty . ' ' . $item->product_name;
        }

        if ($rest_qty > 0) {

            $rest_items[] =
                $rest_qty . ' ' . $item->product_name;
        }
    }

    // ═════════════════════════════════════════════
    // PAYMENT LOGIC
    // ═════════════════════════════════════════════

    $totalAmount = $order->total_amount;

    $totalPaid = 0;

    $paymentRows = [];

    $payments = InvoicePayment::whereHas(
        'invoice',
        function ($q) use ($order) {
            $q->where('order_id', $order->id);
        }
    )
    ->orderBy('created_at', 'asc')
    ->get();

    foreach ($payments as $p) {

        $totalPaid += $p->paid_amount;

        $remaining = max(
            0,
            $totalAmount - $totalPaid
        );

        $actualRest = ($remaining == 0)
            ? 0
            : $p->rest_amount;

        $paymentRows[] = [

            'date' => Carbon::parse(
                $p->created_at
            )->format('d.m.Y'),

            'pay' => number_format(
                $p->paid_amount,
                2
            ),

            'total_rest' => number_format(
                $remaining,
                2
            ),

            'act_rest' => number_format(
                $actualRest,
                2
            ),

            'signature' => '',
        ];
    }

    // ═════════════════════════════════════════════
    // VIEW DATA
    // ═════════════════════════════════════════════
    // dd($order->customer->company_name);
    $data = [

        // HEADER

        'order_no' => $order->order_number,

        'last_order_no' =>
            $last_order->order_number ?? 'N/A',

        'next_order_no' =>
            $next_order->order_number ?? '',

        'name' => $order->customer_name ?? '',

        'rank' => $order->customer->employee_rank ?? '',
        
        'company_name' => $order->customer->company_name ?? '',

        'address' => $order->billing_address ?? '',

        'telephone' => (
            ($order->customer ? $order->customer->country_code_phone : '') . 
            ($order->customer ? $order->customer->phone : '')
        ),

        // ITEMS

        'items' => $items,

        // TOTALS

        'amount' => number_format(
            $order->total_amount,
            2,
            ',',
            ''
        ),

        'net_qty' => $net_qty,

        // PAYMENTS

        'paymentRows' => $paymentRows,

        // LEGACY

        'item_sold' => implode('+', $item_sold),

        'rest_items' => implode('+', $rest_items),

        'status' => implode('+', $item_delivered),
    ];

    // ═════════════════════════════════════════════
    // GENERATE PDF
    // ═════════════════════════════════════════════

    $pdf = Pdf::loadView(
        'invoice.product_delivery',
        $data
    )->setPaper('A4');

    return response($pdf->output(), 200)

        ->header(
            'Content-Type',
            'application/pdf'
        )

        ->header(
            'Content-Disposition',
            'inline; filename="product_delivery.pdf"'
        );
}
}
