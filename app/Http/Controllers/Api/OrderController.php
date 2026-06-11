<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;
use App\Models\{
    User,Product, Order,OrderMultipleFile, OrderItem, OrderMeasurement, UserWhatsapp, UserAddress,
    SalesmanBilling, Page, Collection, Category, SubCategory, Fabric,
    CataloguePageItem, OrderItemCatalogueImage, OrderItemVoiceMessage, Measurement,Ledger,PaymentCollection,TodoList,Journal,Payment
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\AccountingRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class OrderController extends Controller
{
    protected $accountingRepository;

    // public function __construct(AccountingRepositoryInterface $accountingRepository,)
    // {
    //     $this->accountingRepository = $accountingRepository;
    // }

    protected function getAuthenticatedUser()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user; // Return the response if the user is not authenticated
        }

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'search'     => 'nullable|string|max:255',
        ]);
        
        $ordersQuery=Order::where('created_by',$user->id);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $ordersQuery->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Fetch the filtered orders
        $orders = $ordersQuery->orderBy('id', 'DESC')->get();
        $orders->transform(function ($order) {
            $order->order_number = 'RI-' . $order->order_number;
            return $order;
        });

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No orders found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order list fetched successfully',
            'data' => $orders,
        ]);
       

    }
    
   
    
    public function detail(Request $request)
    {
        $user = $this->getAuthenticatedUser();
    
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }
    
        $data = Order::where('id', $request->id)
            ->where('created_by', $user->id)
            ->with('items','items.measurements')
            ->first(); 
    
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'No data found!'
            ]);
        }
    
        $data->order_number = 'RI-' . $data->order_number;
    
        return response()->json([
            'status' => 'success',
            'message' => 'Order detail fetch successfully.',
            'data' => $data,
        ]);
    }
        
       


   
    
    
       public function fetchNextOrderId(Request $request)
    {
        try {
    
            // Check logged in user
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized user'
                ], 401);
            }
    
            // Call Helper Function
            $bill = Helper::generateInvoiceBill($user->id);
            // Validate bill response
            if (!$bill || $bill['number'] == '000') {
                return response()->json([
                    'status' => false,
                    'message' => 'Billing limit exceeded or billing book not found'
                ], 400);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Next order id generated successfully',
                'data' => [
                    'salesman_id' => $user->id,
                    'salesman_name' => $user->name,
                    'order_number' => "RI-".$bill['number'],
                    'bill_id' => $bill['bill_id']
                ]
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
      public function skipOrder(Request $request)
    {
        // Only validate status and reason
        $validator = Validator::make($request->all(), [
            'skip_order_reason' => 'required|string',
            'status' => 'required|in:Cancelled,On Hold'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
    
        DB::beginTransaction();
    
        try {
            $user = auth()->user();
    
            // Generate next order number and get bill
            $bill = Helper::generateInvoiceBill($user->id);
            if (!$bill || $bill['number'] == '000') {
                return response()->json([
                    'status' => false,
                    'message' => 'Billing limit exceeded or billing book not found'
                ], 400);
            }
    
            $orderNumber =  $bill['number'];
            $billId = $bill['bill_id'];
    
            // Create order
            $order = new Order();
            $order->order_number = $orderNumber;
            $order->bill_id = $billId; // save the bill_id
            $order->status = $request->status;
            $order->skip_order_reason = $request->skip_order_reason;
            $order->created_by = $user->id;
            $order->save();
    
            // Increment bill usage
            $billBook = SalesmanBilling::find($billId);
            if ($billBook) {
                $billBook->increment('no_of_used');
            }
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => $request->status == 'On Hold'
                    ? 'Order placed on HOLD successfully'
                    : 'Order skipped (cancelled) successfully',
                'data' => [
                    'order_number' => $order->order_number
                ]
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error skipping order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    
   
    
    public function createOrder(Request $request)
{
    DB::beginTransaction();

    try {

        $validated = $request->validate([
            // Customer
            'name'              => 'nullable|string',
            'phone'             => 'nullable|string',
            'billing_address'   => 'nullable|string',

            // Payment
            'air_mail'          => 'nullable|numeric|min:0',
            'paid_amount'       => 'nullable|numeric|min:0',

            'customer_id'       => 'nullable|integer',

            'customer_image'    => 'required',
            'customer_image.*'  => 'image',

            'bill_book_copy'    => 'required',
            'bill_book_copy.*'  => 'mimes:jpg,jpeg,png,webp,pdf',

            'verified_video'    => 'nullable',
            'verified_video.*'  => 'mimes:mp4,avi,mov',

            'verified_audio'    => 'nullable',
            'verified_audio.*'  => 'file',

            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:1',
            'items.*.price'         => 'required|numeric|min:0',
        ]);

        // SAFE ACCESS
        $airMail    = $validated['air_mail'] ?? 0;
        $paidAmount = $validated['paid_amount'] ?? 0;

        // TOTAL CALCULATION
        $totalProductAmount = collect($validated['items'])->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $totalAmount = $totalProductAmount + $airMail;
        $pending     = max(0, $totalAmount - $paidAmount);

        $user = auth()->user();
        $salesmanId = $user->id;

        // ================= CUSTOMER LOGIC FIXED =================
        $customerId    = null;
        $prefix        = null;
        $customerName  = null;
        $customerPhone = null;

        if (!empty($validated['customer_id'])) {

            $customer = User::find($validated['customer_id']);

            if (!$customer) {
                throw new \Exception("Customer not found");
            }

            $customerId    = $customer->id;
            $prefix        = $customer->prefix;
            $customerName  = $customer->name;
            $customerPhone = $customer->phone;

        } else {

            $customerName  = $validated['name'] ?? null;
            $customerPhone = $validated['phone'] ?? null;

            // $customer = User::create([
            //     'name'  => $customerName,
            //     'phone' => $customerPhone,
            // ]);

            // $customerId = $customer->id;
            

            // Generate PIN
            $plainPin = rand(10000, 99999);
            
            // Welcome bonus
            $welcomeBonus = config('loyalty.welcome_bonus', 0);
            
            // CREATE CUSTOMER
            $customer = User::create([
                'name'                 => $customerName,
                'phone'                => $customerPhone,
                'user_type'                 => 1,
            
                // UNIQUE QR
                'qr_code'              => Str::uuid(),
            
                // UNIQUE CARD NUMBER
                'card_number'          => 'CARD' . time() . rand(10, 99),
            
                // PIN
                'pin'                  => $plainPin,
            
                // BONUS
                'total_points'         => $welcomeBonus,
            ]);
            
            $customerId = $customer->id;
            
            
            // WELCOME BONUS ENTRY
            if ($welcomeBonus > 0) {
            
                $expiryDays = config('loyalty.point_expiry_days', 365);
            
                WalletTransaction::create([
                    'user_id'     => $customer->id,
                    'type'        => 'credit',
                    'points'      => $welcomeBonus,
                    'source'      => 'bonus',
                    'expiry_date' => now()->addDays($expiryDays),
                ]);
            }
        }

        // BILL GENERATION
        $billData    = Helper::generateInvoiceBill($salesmanId);
        $orderNumber = $billData['number'];
        $billId      = $billData['bill_id'];

        // ADDRESS
        $address = UserAddress::where('user_id', $customerId)
            ->where('address_type', 1)
            ->first();

        $billingAddress = $address->address ?? ($validated['billing_address'] ?? null);

        // CREATE ORDER
        $order = Order::create([
            'order_number'          => $orderNumber,
            'prefix'                => $prefix,
            'customer_name'         => $customerName,
            'customer_email'        => null,
            'customer_id'           => $customerId,
            'billing_address'       => $billingAddress,
            'total_product_amount'  => $totalProductAmount,
            'air_mail'              => $airMail,
            'paid_amount'          => $paidAmount,
             'last_payment_date'     => $paidAmount > 0 ? now() : null,

            'total_amount'          => $totalAmount,
            'created_by'            => $salesmanId,
            'team_lead_id'          => $user->parent_id ?? null,
        ]);

        // FILE UPLOADS
        $fileTypes = [
            'customer_image' => 'client_image',
            'bill_book_copy' => 'bill_book_copy',
            'verified_video' => 'verified_video',
            'verified_audio' => 'verified_audio',
        ];

        foreach ($fileTypes as $field => $folder) {

            $files = $request->file($field);

            if ($files) {

                $files = is_array($files) ? $files : [$files];
                $paths = [];

                foreach ($files as $file) {
                    $paths[] = Helper::handleFileUpload($file, $folder);
                }

                OrderMultipleFile::create([
                    'order_id'  => $order->id,
                    'file_type' => $field,
                    'file_path' => implode(',', $paths),
                ]);
            }
        }

        // ORDER ITEMS
        foreach ($validated['items'] as $item) {
            //  Fetch product
          $product = Product::find($item['product_id']);

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $item['product_id'],
                'product_name'  => $product->name ?? null,
                'quantity'     => $item['quantity'],
                'piece_price'  => $item['price'],
                'total_price'  => $item['price'] * $item['quantity'],
                'status'       => 'Hold'
            ]);
        }

        // PAYMENT (SAFE)
        if ($paidAmount > 0 && $customerId) {

            $voucherNo = 'PAYRECEIPT' . time();

            $payment_id = Payment::insertGetId([
                'customer_id'  => $customerId,
                'payment_for'  => 'credit',
                'voucher_no'   => $voucherNo,
                'payment_date' => now()->toDateString(),
                'payment_in'   => 'cash',
                'bank_cash'    => 'cash',
                'payment_mode' => 'cash',
                'amount'       => $paidAmount,
                'narration'    => 'Advance payment for order #' . $orderNumber,
                'created_by'   => $salesmanId,
                'created_from' => 'app',
                'created_at'   => now(),
            ]);

            Ledger::insert([
                'user_type'          => 'customer',
                'customer_id'        => $customerId,
                'transaction_id'     => $voucherNo,
                'transaction_amount' => $paidAmount,
                'payment_id'         => $payment_id,
                'bank_cash'          => 'cash',
                'is_credit'          => 1,
                'is_debit'           => 0,
                'entry_date'         => now()->toDateString(),
                'purpose'            => 'payment_receipt',
                'purpose_description'=> 'Advance payment against order #' . $orderNumber,
                'created_at'         => now(),
            ]);

            Journal::insert([
                'transaction_amount'  => $paidAmount,
                'is_credit'           => 1,
                'is_debit'            => 0,
                'entry_date'          => now()->toDateString(),
                'payment_id'          => $payment_id,
                'bank_cash'           => 'cash',
                'purpose'             => 'payment_receipt',
                'purpose_description' => 'Advance payment against order #' . $orderNumber,
                'purpose_id'          => $voucherNo,
                'created_at'          => now(),
            ]);

            PaymentCollection::insert([
                'customer_id'       => $customerId,
                'user_id'           => $salesmanId,
                'payment_id'        => $payment_id,
                'collection_amount' => $paidAmount,
                'payment_type'      => 'cash',
                'voucher_no'        => $voucherNo,
                'cheque_date'       => now()->toDateString(),
                'is_ledger_added'   => 0,
                'is_approve'        => 0,
                'is_settled'        => 0,
                'created_from'      => 'app',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Order created successfully',
            'data' => [
                'order_number' => $orderNumber,
                'bill_id' => $billId,
                'order_id' => $order->id,
                'total_product_amount' => $totalProductAmount,
                'air_mail' => $airMail,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pending,
                'items' => $order->items()->get(),
            ]
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'Order creation failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    //customer order list
    public function customer_order_list(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $filter = $request->filter;
        $customer_id = $request->customer_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $status = $request->status;

        $auth = $user; 

        $ordersQuery = Order::query();

   
        if (!empty($customer_id)) {
            $ordersQuery->where('customer_id', $customer_id);
        }

        if (!empty($filter)) {
            $ordersQuery->where(function ($query) use ($filter) {
                $query->where('order_number', 'like', "%{$filter}%")
                    ->orWhereHas('customer', function ($q2) use ($filter) {
                        $q2->where(function ($sub) use ($filter) {
                            $sub->where('name', 'like', "%{$filter}%")
                                ->orWhere('email', 'like', "%{$filter}%")
                                ->orWhere('phone', 'like', "%{$filter}%")
                                ->orWhere('whatsapp_no', 'like', "%{$filter}%");
                        });
                    });
            });
        }

        if (!empty($start_date) && !empty($end_date)) {
            $ordersQuery->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay()
            ]);
        }

        if (!empty($status)) {
            $ordersQuery->where('status', $status);
        }

        if (!$auth->is_super_admin) {
            $ordersQuery->where(function ($subQuery) use ($auth) {
                $subQuery->where('created_by', $auth->id)
                        ->orWhere('team_lead_id', $auth->id);
            });
        }

        $orders = $ordersQuery
            ->with([
                'items.product:id,name',
                'items.collectionType:id,title',
                'items.categoryInfo:id,title'
            ])
            ->orderBy('created_at', 'desc')
            ->get();    


        $data = $orders->map(function ($item) {
            $orderTime = Carbon::parse($item->created_at);

            if ($orderTime->isToday()) {
                $formattedOrderTime = "Today " . $orderTime->format('h:i A');
            } elseif ($orderTime->isYesterday()) {
                $formattedOrderTime = "Yesterday " . $orderTime->format('h:i A');
            } else {
                $formattedOrderTime = $orderTime->format('d M y h:i A');
            }

            return [
                'order_id' => $item->id,
                'customer_name' => trim(($item->prefix ?? '') . ' ' . ($item->customer_name ?? '')),
                'order_number' => $item->order_number,
                'order_amount' => $item->total_amount,
                'order_time' => $formattedOrderTime,
                'order_item' => $item->items->map(function ($orderItem) use ($item) {
                    return [
                        'id' => $orderItem->id,
                        'order_number' => $item->order_number,
                        'product_name' => $orderItem->product->name ?? null,
                        'collection_name' => optional($orderItem->collectionType)->title,
                        'category_name' => optional($orderItem->categoryInfo)->title,
                        'quantity' => $orderItem->quantity,
                        'piece_price' => $orderItem->piece_price,
                        'total_price' => $orderItem->total_price,
                        'priority_level' => $orderItem->priority_level,
                        'expected_delivery_date' => $orderItem->expected_delivery_date,
                        'status' => $orderItem->status,
                        'tl_status' => $orderItem->tl_status,
                        'admin_status' => $orderItem->admin_status,
                        'created_at' => $orderItem->created_at,
                        'updated_at' => $orderItem->updated_at,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Order information fetched successfully!',
            'orders' => $data,
        ]);
    }
    //ledger view
    public function ledgerView(Request $request)
    {

        // 1. Get and Validate Request Parameters
        $userType = $request->input('user_type'); 
        $userId = $request->input('user_id'); // Can be staff_id, customer_id, or supplier_id
        $fromDate = $request->input('from_date', date('Y-m-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));
        $bankCash = $request->input('bank_cash');
        if (!$userType || !$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'The user_type and user_id fields are required.'
            ], 400);
        }

        // 2. Data Fetching Logic (Mirrors Livewire's LedgerUserData)

        $opening_bal_query = Ledger::query();
        $query = Ledger::query();
        $opening_bal_showable = 1;
        $day_opening_amount = 0;
        $errorMessage = [];

        // Apply Date Filters for Transaction Data
        $query->whereDate('entry_date', '>=', $fromDate);
        $query->whereDate('entry_date', '<=', $toDate);

        // Apply User Filters
        if ($userType === 'staff') {
            $user = User::where('user_type', 0)->find($userId);
            if (!$user) { $errorMessage['staff'] = 'Staff not found.'; }
            $query->where('staff_id', $userId);
            $opening_bal_query->where('staff_id', $userId);
        } elseif ($userType === 'customer') {
            $user = User::where('user_type', 1)->find($userId);
            if (!$user) { $errorMessage['customer'] = 'Customer not found.'; }
            $query->where('customer_id', $userId);
            $opening_bal_query->where('customer_id', $userId);
        } elseif ($userType === 'supplier') {
            $user = Supplier::find($userId);
            if (!$user) { $errorMessage['supplier'] = 'Supplier not found.'; }
            $query->where('supplier_id', $userId);
            $opening_bal_query->where('supplier_id', $userId);
        } else {
             return response()->json([
                'status' => 'error',
                'message' => 'Invalid user_type provided.'
            ], 400);
        }

        if (!empty($errorMessage)) {
             return response()->json([
                'status' => 'error',
                'message' => array_values($errorMessage)[0]
            ], 404);
        }
        
        // Logic for Opening Balance Date Range (Mirroring Livewire component)
        $opening_bal_date_end = date('Y-m-d', strtotime('-1 day', strtotime($fromDate)));

        if ($userType === 'customer') {
            $check_ob_exist_customer = Ledger::where('purpose', 'opening_balance')
                ->where('user_type', 'customer')
                ->where('customer_id', $userId)
                ->orderBy('id', 'asc')
                ->first();

            if (!empty($check_ob_exist_customer)) {
                if ($fromDate == $check_ob_exist_customer->entry_date) {
                    $opening_bal_showable = 0;
                    $opening_bal_query->whereDate('entry_date', $check_ob_exist_customer->entry_date);
                } else {
                    $opening_bal_date_start = $check_ob_exist_customer->entry_date;
                    // Filter between OB entry date and $fromDate - 1 day
                    $opening_bal_query->whereRaw(" entry_date BETWEEN '{$opening_bal_date_start}' AND '{$opening_bal_date_end}'");
                }
            } else {
                // Filter all entries before $fromDate
                $opening_bal_query->whereDate('entry_date', '<=', $opening_bal_date_end);
            }
        } else {
            // For Staff/Supplier, filter all entries before $fromDate
            $opening_bal_query->whereDate('entry_date', '<=', $opening_bal_date_end);
        }
        
        // Calculate Opening Balance Amount
        $opening_bal = $opening_bal_query->orderBy('entry_date', 'ASC')->orderBy('updated_at', 'ASC')->get();
        foreach ($opening_bal as $ob) {
            if (!empty($ob->is_credit)) {
                $day_opening_amount += $ob->transaction_amount;
            }
            if (!empty($ob->is_debit)) {
                $day_opening_amount -= $ob->transaction_amount;
            }
        }

        // Apply Bank/Cash Filter
        if (!empty($bankCash)) {
            $query->where('bank_cash', $bankCash);
        }

        $ledgerData = $query->orderBy('entry_date', 'ASC')->get();


        // 3. Structure the Response Data

        $net_value = $day_opening_amount;
        $transactions = [];

        // Add Opening Balance Row
        if ($opening_bal_showable == 1) {
             $getCrDrOB = Helper::getCrDr($day_opening_amount);
             $deb_ob_amount = ($getCrDrOB === 'Dr') ? Helper::replaceMinusSign($day_opening_amount) : 0;
             $cred_ob_amount = ($getCrDrOB === 'Cr') ? $day_opening_amount : 0;
            
            $transactions[] = [
                'date' => date('d-m-Y', strtotime($fromDate)),
                'purpose' => 'Opening Balance',
                'debit' => $deb_ob_amount > 0 ? number_format($deb_ob_amount, 2, '.', '') : null,
                'credit' => $cred_ob_amount > 0 ? number_format($cred_ob_amount, 2, '.', '') : null,
                'balance' => number_format(Helper::replaceMinusSign($net_value), 2, '.', ''),
                'balance_type' => Helper::getCrDr($net_value)
            ];
        }

        // Process Ledger Transactions
        foreach ($ledgerData as $item) {
            $debit_amount = null;
            $credit_amount = null;

            if (!empty($item->is_credit)) {
                $credit_amount = $item->transaction_amount;
                $net_value += $item->transaction_amount;
            }

            if (!empty($item->is_debit)) {
                $debit_amount = $item->transaction_amount;
                $net_value -= $item->transaction_amount;
            }
            
            $purpose_with_mode = ucwords(str_replace('_', ' ', $item->purpose)) . ' (' . ucwords($item->bank_cash) . ')';

            $transactions[] = [
                'id' => $item->id,
                'transaction_id' => $item->transaction_id,
                'date' => date('d-m-Y', strtotime($item->created_at)),
                'purpose' => $purpose_with_mode,
                'remarks' => $item->purpose_description, 
                'debit' => $debit_amount ? number_format((float) $debit_amount, 2, '.', '') : null,
                'credit' => $credit_amount ? number_format((float) $credit_amount, 2, '.', '') : null,
                'balance' => number_format(Helper::replaceMinusSign($net_value), 2, '.', ''),
                'balance_type' => Helper::getCrDr($net_value),
            ];
        }

        // 4. Final API Response

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $userId,
                'type' => $userType,
                'name' => $user->name ?? 'N/A'
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'bank_cash' => $bankCash
            ],
            'ledger_entries' => $transactions,
            'closing_balance' => [
                'amount' => number_format(Helper::replaceMinusSign($net_value), 2, '.', ''),
                'type' => Helper::getCrDr($net_value)
            ]
        ]);
    }

     /**
     * Store Payment Collection API
     */
    // public function paymentReceiptSave(Request $request)
    // {
    //     // Base rules
    //     $rules = [
    //         'customer_id'       => 'required|exists:users,id',
    //         'staff_id'          => 'required|exists:users,id',
    //         'collection_amount' => 'required|numeric|min:0.01',
    //         'payment_type'      => 'required|in:cash,cheque,digital_payment,neft',
    //         'voucher_no'        => 'nullable|string',
    //         'payment_date'      => 'required|date',
    //         'next_payment_date' => 'nullable|date',
    //         'deposit_date'      => 'nullable|date',
    //         'payment_collection_id' => 'nullable|integer|exists:payment_collections,id',
    //     ];

    //     // add conditional rules
    //     if ($request->payment_type === 'cheque') {
    //         $rules['cheque_number'] = 'required|string|max:255';
    //         // deposit_date may be required depending on business rules; include as needed
    //         $rules['deposit_date'] = 'required|date';
    //         $rules['cheque_file'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
    //     } elseif ($request->payment_type === 'digital_payment') {
    //         $rules['transaction_no'] = 'required|string|max:255';
    //         $rules['withdrawal_charge'] = 'required|numeric|min:0';
    //     } elseif ($request->payment_type === 'neft') {
    //         $rules['cheque_number'] = 'required|string|max:255';
    //     }

    //     // If updating an existing collection, avoid voucher duplicate rule on same record
    //     if ($request->filled('payment_collection_id')) {
    //         $rules['voucher_no'] .= ',voucher_no,' . $request->payment_collection_id . ',id';
    //     } else {
    //         $rules['voucher_no'] .= '|unique:payment_collections,voucher_no';
    //     }

    //     $validator = Validator::make($request->all(), $rules);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();
    //         $customer = User::find($request->customer_id);
    //         if(!$customer){
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Customer not found in users table.'
    //             ],422);
    //         }
    //         // Prepare data mapping exactly as repository expects
    //         $data = [
    //             'customer_id'           => $customer->id,
    //             'staff_id'              => $request->staff_id,        
    //             'amount'                => $request->collection_amount,
    //             'payment_mode'          => $request->payment_type,    // repository expects 'payment_mode'
    //             'voucher_no'            => 'PAYRECEIPT'.time(),
    //             'payment_date'          => $request->payment_date,
    //             'receipt_for'           => $request->input('receipt_for', 'Customer'), // default
    //             'payment_collection_id' => $request->payment_collection_id ?? null,
    //             'credit_date'           => $request->credit_date ?? null, // optional
    //             'next_payment_date'     => $request->next_payment_date ?? null,
    //             'deposit_date'          => $request->deposit_date ?? null,
    //         ];
    //         // dd($data);

    //         // Payment-mode specific fields
    //         if ($request->payment_type === 'cheque') {
    //             $data['chq_utr_no'] = $request->cheque_number;
    //             $data['bank_name']  = $request->bank_name ?? null;
    //             // file upload
    //             if ($request->hasFile('cheque_file')) {
    //                 $ext = $request->file('cheque_file')->getClientOriginalExtension();
    //                 $filename = Str::random(10) . '.' . $ext;
    //                 $path = $request->file('cheque_file')->storeAs('uploads/cheque', $filename, 'public');
    //                 $data['cheque_photo'] = 'storage/' . $path;
    //             } elseif ($request->filled('cheque_photo')) {
    //                 // accept pre-uploaded path if provided by client
    //                 $data['cheque_photo'] = $request->cheque_photo;
    //             }
    //         } elseif ($request->payment_type === 'digital_payment') {
    //             $data['transaction_no'] = $request->transaction_no;
    //             $data['withdrawal_charge'] = $request->withdrawal_charge ?? 0;
    //             $data['bank_name'] = $request->bank_name ?? null; 
    //             $data['chq_utr_no'] = $request->cheque_number;
    //         }
    //         elseif ($request->payment_type === 'neft') {
    //             $data['bank_name'] = $request->bank_name ?? null; 
    //             $data['chq_utr_no'] = $request->cheque_number;
    //         } else { // cash
    //             // ensure bank fields are empty for cash (repository uses bank_name/chq_utr_no optionally)
    //             $data['bank_name'] = null;
    //             $data['chq_utr_no'] = null;
    //             $data['transaction_no'] = null;
    //             $data['withdrawal_charge'] = null;
    //         }

    //         // keep backward compatibility: repository sometimes uses 'payment_id' when updating â€” allow passing it
    //         if ($request->filled('payment_id')) {
    //             $data['payment_id'] = $request->payment_id;
    //         }

    //         // call repository (it will insert/update payment, ledger, journals & invoice payments)
    //         $this->accountingRepository->StorePaymentReceipt($data);

    //         // create todos if required (mirrors your Livewire logic)
    //         $admin_id = Auth::guard('admin')->check() ? Auth::guard('admin')->id() : Auth::id();
    //         if (!empty($data['next_payment_date'])) {
    //             TodoList::create([
    //                 'user_id' => $data['staff_id'],
    //                 'customer_id' => $data['customer_id'],
    //                 'created_by' => $admin_id,
    //                 'todo_type' => 'Payment',
    //                 'todo_date' => $data['next_payment_date'],
    //                 'remark' => 'Next Payment Schedule on ' . $data['next_payment_date'],
    //             ]);
    //         }
    //         if (!empty($data['deposit_date'])) {
    //             TodoList::create([
    //                 'user_id' => $data['staff_id'],
    //                 'customer_id' => $data['customer_id'],
    //                 'created_by' => $admin_id,
    //                 'todo_type' => 'Cheque Deposit',
    //                 'todo_date' => $data['deposit_date'],
    //                 'remark' => 'Deposit Date ' . $data['deposit_date'],
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Payment collection stored successfully.',
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to store payment collection.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

   public function skipOrderBill(Request $request)
    {
        $validated = $request->validate([
            'skip_order_reason' => 'required|string',
            'salesman_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // Generate order number and get bill ID automatically
            $billData = Helper::generateInvoiceBill($validated['salesman_id']);
            $orderNumber = $billData['number'];
            $billId = $billData['bill_id'];

            if ($orderNumber === '000' || !$billId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active bill book available for this salesman.'
                ], 400);
            }

            // Create cancelled order
            $order = new Order();
            $order->order_number = $orderNumber;
            $order->status = 'Cancelled';
            $order->skip_order_reason = $validated['skip_order_reason'];
            $order->created_by = $validated['salesman_id'];
            $order->save();

            // Increment used count
            $billBook = SalesmanBilling::find($billId);
            $billBook->increment('no_of_used');

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order skipped successfully.',
                'data' => [
                    'order_number' => $orderNumber,
                    'bill_id' => $billId
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error skipping order: ' . $e->getMessage()
            ], 500);
        }
    }


    public function downloadBill($orderId)
    {
        $order = Order::with([
            'items',
            'customer.billingAddressLatest',
            'createdBy'
        ])->find($orderId);
        
        // Generate PDF
        $pdf = Pdf::loadView('api_order_bill.order_bill_pdf', compact('order'));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="bill_' . $order->order_number . '.pdf"'
            );
    }
   
   
    
   
}
