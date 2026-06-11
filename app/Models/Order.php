<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChangeLog;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\Auth;
use App\Services\ChangeTracker;

class Order extends Model
{
    use HasFactory;


    protected $table = 'orders';
    public $_relatedChanges = [];
    protected $fillable = [
        'bill_id',
        'customer_id',
        'business_type',
        'order_number',
        'prefix',
        'customer_name',
        'customer_email',
        'customer_image',
        'billing_address',
        'billing_landmark',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_pin',
        'shipping_address',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'last_payment_date',
        'payment_mode',
        'status',
        'business_type',
        'created_by' ,
        'team_lead_id',
        'country_code_alt_1',
        'alternative_phone_number_1',
        'country_code_alt_2',
        'alternative_phone_number_2',
        'country_code_whatsapp',
        'country_code_phone',
        'source',
        'reference',
        'ht_amount',
        'tva_amount',
        'ca_amount',
        'due_date',
        'invoice_date',
        'invoice_type',
        'total_product_amount',
        'air_mail',
        'physical_order_bill_book',
        'verified_video',
        'verified_audio'
    ];
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function measurements()
    {
        return $this->hasMany(OrderMeasurement::class);
    }
    public function measurement()
    {
        return $this->belongsTo(Measurement::class, 'measurement_id');
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function packingslip()
    {
        return $this->hasOne(PackingSlip::class, 'order_id', 'id');
    }
    public function businessType()
    {
        return $this->belongsTo(BusinessType::class, 'business_type');
    }
    
    public function files()
    {
        return $this->hasMany(OrderMultipleFile::class);
    }


    protected $status_classes = [
        "Partial Approved By Admin"                 => ["Partial Approved By Admin", "partial_approved_order_by_admin"],
        "Fully Approved By Admin"                   => ["Fully Approved By Admin", "fully_approved_order_by_admin"],
        "Ready for Delivery"               => ["Ready for Delivery", "ready_for_delivery"],
        "Cancelled"                        => ["Cancelled", "order_cancelled"],
        "On Hold"                        => ["On Hold", "order_on_hold"],
        "Returned"                         => ["Returned", "order_returned"],
        "Received by Sales Team"           => ["Received by Sales Team", "received_by_sales_team"],
        "Delivered to Customer"            =>["Delivered to Customer","delivered_to_customer"],
        "Partial Delivered to Customer"    =>["Partial Delivered to Customer","partial_delivered_to_customer"],
        "Approval Pending from TL"         => ["Approval Pending from TL", "approval_pending_from_tl"],
        "Received at Production"           => ["Received at Production", "received_at_production"],
        "Partial Delivered By Production"  => ["Partial Delivered By Production", "partial_delivered_by_production"],
        "Fully Delivered By Production"    => ["Fully Delivered By Production", "fully_delivered_by_production"],
        "Partial Approved By TL"           => ["Partial Approved By TL", "partial_approved_by_tl"],
        "Fully Approved By TL"             => ["Fully Approved By TL", "fully_approved_by_tl"],

    ];

    // Accessor to get status label
    public function getStatusLabelAttribute()
    {
        

        $order_status = $this->attributes['status'] ?? 'Returned'; // Default to "Returned"
        return $this->status_classes[$order_status][0] ?? "Unknown"; // Fallback to "Unknown"
    }

    // Accessor to get status class
    public function getStatusClassAttribute()
    {
       

        $order_status = $this->attributes['status'] ?? 'Returned'; // Default to "Returned"
        return $this->status_classes[$order_status][1] ?? "muted"; // Default class if not found
    }

   

    public function allItemsAssigned()
    {
        // return true only if NO item has assigned_team = NULL
        return !$this->items()->whereNull('assigned_team')->exists();
    }

    // In the Order model

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'id');
    }

   

    // TL can approve if there are any 'Process' items not yet invoiced
    public function canTLApprove()
    {
        return $this->items()
            ->where('status', 'Process')
            ->where(function ($query) {
                $query->whereNull('tl_status')
                    ->orWhere('tl_status', '!=', 'Approved');
            })
            ->exists();
    }

    // Admin can approve only if 'Process' + 'tl_status' = 'Approved'
    public function canAdminApprove()
    {
       return $this->allItemsAssigned() && $this->items()
            ->where('status', 'Process')
            ->where('tl_status', 'Approved')
            ->where(function($q) {
                $q->whereNull('admin_status')->orWhere('admin_status', '!=', 'Approved');
            })
            ->whereNull('assigned_team')
            ->exists();
    }

    public function hasHoldItemsWithApprovedTLStatus()
    {
        $hasHold = $this->items()->where('status', 'Hold')->exists();

        $hasApprovedProcess = $this->items()
            ->where('status', 'Process')
            ->where('tl_status', 'Approved')
            ->exists();

        return $hasHold && $hasApprovedProcess;
    }

    public function hasHoldItemsWithApprovedByAdmin()
    {
        $hasHold = $this->items()->where('status', 'Hold')->exists();

        $hasFullyApproved = $this->items()
            ->where('status', 'Process')
            ->where('tl_status', 'Approved')
            ->where('admin_status', 'Approved')
            ->exists();

        return $hasHold && $hasFullyApproved;
    }


    // Check if order has at least one Process item
    public function hasProcessItems()
    {
        return $this->items()->where('status', 'Process')->exists();
    }
    
    // Check if order has at least one Hold item
    public function hasHoldItems()
    {
        return $this->items()->where('status', 'Hold')->exists();
    }
    
    // Optional: Check if order has both Process and Hold items
    public function hasProcessAndHoldItems()
    {
        return $this->hasProcessItems() && $this->hasHoldItems();
    }


     public function hasProcessAndTLApprovedItems(){
         return $this->items()
        ->where('status', 'Process')
        ->where('tl_status', 'Approved')
        ->where(function ($q) {
            $q->whereNull('admin_status')
              ->orWhere('admin_status', '!=', 'Approved');
        })
        ->exists();
    }
    
     public function hasProcessAndTLPendingItems(){
         return $this->items()
        ->where('status', 'Process')
        ->where('tl_status', 'Pending')
        ->where(function ($q) {
            $q->whereNull('admin_status')
              ->orWhere('admin_status', '!=', 'Approved');
        })
        ->exists();
    }
    
    
    
     // Admin can approve only items that are Process + TL Approved + Admin not approved
    public function hasProcessAndAdminApprovedItems()
    {
        return $this->items()
            ->where('status', 'Process')           // Only Process items
            ->where('tl_status', 'Approved')      // TL must have approved
            ->where(function ($q) {
                $q->whereNull('admin_status')      // Admin not yet approved
                ->orWhere('admin_status', '!=', 'Approved');
            })
            ->exists();
    }
    
    // Can Sales edit until admin approved all order items
    // public function canSalesEdit()
    // {
    //     return $this->status !== 'Fully Approved By Admin';
    // }
    
    public function canSalesEdit()
    {
        // Get all items for this order
        $items = $this->items; // assuming $this is the Order model with items relation loaded
    
        // Check if all items meet the "fully approved" condition
        $allApproved = $items->every(function ($item) {
            return $item->admin_status === 'Approved' 
                && $item->tl_status === 'Approved'
                && !is_null($item->assigned_team);
        });
    
        // Sales can edit if not all items are fully approved
        return !$allApproved;
    }
    
 


}
