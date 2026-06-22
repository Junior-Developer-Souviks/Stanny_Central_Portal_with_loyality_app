<?php

namespace App\Http\Livewire\Order;
use App\Repositories\OrderRepository;

use Livewire\Component;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Catalogue;
use App\Models\Measurement;
use App\Models\OrderMeasurement;
use App\Models\Fabric;
use App\Models\Ledger;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SalesmanBilling;
use App\Models\Country;
use App\Models\BusinessType;
use App\Models\OrderItemCatalogueImage;
use App\Models\StockFabric;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\UserWhatsapp;
use App\Models\Page;
use App\Models\CataloguePageItem;
use App\Models\OrderItemVoiceMessage;
use App\Models\Changelog;
use App\Models\OrderMultipleFile;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use App\Helpers\Helper;
use Illuminate\Validation\Rule;
use App\Services\ChangeTracker;


class OrderEdit extends Component
{
    use WithFileUploads;

    public $searchTerm = '';
    public $searchResults = [];
    public $errorClass = [];
    public $collections = [];
    public $errorMessage = [];
    public $activeTab = 1;
    public $items = [];

    public $FetchProduct = 1;
    public $maxPages = [];
    public $salesman;

    public $customers = null;
    public $orders;
    public $is_wa_same, $prefix, $name, $company_name,$employee_rank, $email,$customer_image,$verified_video,$verified_audio, $dob, $customer_id, $phone ,$alternative_phone_number_1, $alternative_phone_number_2,
    $phone_code, $selectedCountryWhatsapp, $alt_phone_code_1 , $alt_phone_code_2 ,$mobileLengthPhone, $mobileLengthWhatsapp, $mobileLengthAlt1, $mobileLengthAlt2,
    $countries,$isWhatsappPhone,$isWhatsappAlt1,$isWhatsappAlt2;
    
    public $physical_order_bill_book = [];
    public $physical_order_bill_book_new = [];
    
    public $order_number, $billing_address,$billing_landmark,$billing_city,$billing_state,$billing_country,$billing_pin;

    public $is_billing_shipping_same;

    public $shipping_address,$shipping_landmark,$shipping_city,$shipping_state,$shipping_country,$shipping_pin;

    //  product
    public $categories,$subCategories = [], $products = [], $measurements = [];
    public $selectedCategory = null, $selectedSubCategory = null,$searchproduct, $product_id =null,$collection;
    public $paid_amount = 0;
    public $billing_amount = 0;
    public $remaining_amount = 0;
    public $payment_mode = null;
    public $catalogues = [];
    public $catalogue_page_item = [];
    // salesmanBill
    public $salesmanBill;
    public $mobileLength;
    public $selectedCountryId;
    public $filteredCountries;
    public $Business_type;
    public $selectedBusinessType = "TEXTILES";
    public $search;
    public $country_code;
    public $country_id;
    public $air_mail;
    public $imageUploads = [];
    public $newUploads = [];
    public $existingImages = [];
    public $voiceUploads = [];
    public $existingVideos = [];
    public $extra_measurement = [];
    public $old_customer_image;
    public $customerType = 'new';
    
    // Unified Measurement Controls Panel
    public $isUnifiedViewActive = false;
    public $unifiedMeasurements = [];

    public function onCustomerTypeChange($value){
        $this->customerType = $value;
        if($value == 'new'){
            $this->searchResults = [];
            $this->searchTerm = '';
        }else{
            $this->searchResults = [];
        }
    }
    
   
    
     public function FindCustomer($term)
    {
        $this->reset('searchResults');
    
        if (!empty($term)) {
    
            $users = User::where('user_type', 1)
                ->where('status', 1)
                ->where(function ($query) use ($term) {
    
                    $query->where('name', 'like', '%' . $term . '%')
                        ->orWhere('phone', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%')
    
                        // Search by order number
                        ->orWhereHas('customer_order', function ($q) use ($term) {
                            $q->where('order_number', 'like', '%' . $term . '%');
                        });
    
                })
                ->take(20)
                ->get();
    
            $this->searchResults = $users->unique('id')->values();
    
        } else {
    
            $this->reset([
                'searchResults',
                'orders',
                'prefix',
                'phone_code',
                'selectedCountryWhatsapp',
                'alt_phone_code_1',
                'alt_phone_code_2',
                'isWhatsappPhone',
                'isWhatsappAlt1',
                'isWhatsappAlt2'
            ]);
        }
    }
    
    public function selectCustomer($customerId)
    {
        $this->resetForm(); // Reset form to default values

        $customer = User::find($customerId);
        if ($customer) {
            // Populate customer details
            $this->customer_id = $customer->id;
            $this->prefix = $customer->prefix;
            $this->name = $customer->name;
            $this->company_name = $customer->company_name;
            $this->employee_rank = $customer->employee_rank;
            $this->email = $customer->email;
            $this->dob = $customer->dob;
            $this->phone = $customer->phone;
            $this->phone_code = $customer->country_code_phone;
            $this->alt_phone_code_1 = $customer->country_code_alt_1;
            $this->alternative_phone_number_1 = $customer->alternative_phone_number_1;
            $this->alt_phone_code_2 = $customer->country_code_alt_2;
            $this->alternative_phone_number_2 = $customer->alternative_phone_number_2;

            if($this->phone_code){
                $this->CountryCodeSet('#mobile', $this->phone_code, $this->phone);
            }
            if($this->alt_phone_code_1){
                $this->CountryCodeSet('#alt_phone_1', $this->alt_phone_code_1,$this->alternative_phone_number_1);
            }
            if($this->alt_phone_code_2){
                $this->CountryCodeSet('#alt_phone_2', $this->alt_phone_code_2,$this->alternative_phone_number_2);
            }
            
            $this->updateMobileLengths();
            // Fetch billing address (address_type = 1)
            $billingAddress = $customer->address()->where('address_type', 1)->first();
            $this->populateAddress('billing', $billingAddress);

            // Fetch shipping address (address_type = 2)
            $shippingAddress = $customer->address()->where('address_type', 2)->first();
            $this->populateAddress('shipping', $shippingAddress);
        }

        // Clear search results after selection
        $this->searchResults = [];
        $this->searchTerm = '';
        $this->dispatch('set-phone-values', [
            'phone' => $this->phone,
            'phone_code' => $this->phone_code,
            'alt_phone_1' => $this->alternative_phone_number_1,
            'alt_phone_code_1' => $this->alt_phone_code_1,
            'alt_phone_2' => $this->alternative_phone_number_2,
            'alt_phone_code_2' => $this->alt_phone_code_2,
        ]);
    }
    
    private function populateAddress($type, $address)
    {
        // dd($address);
        if ($address) {
            $this->{$type . '_address'} = $address->address;
            $this->{$type . '_landmark'} = $address->landmark;
            $this->{$type . '_city'} = $address->city;
            $this->{$type . '_state'} = $address->state;
            $this->{$type . '_country'} = $address->country;
            $this->{$type . '_pin'} = $address->zip_code;
        } else {
            $this->{$type . '_address'} = null;
            $this->{$type . '_landmark'} = null;
            $this->{$type . '_city'} = null;
            $this->{$type . '_state'} = null;
            $this->{$type . '_country'} = null;
            $this->{$type . '_pin'} = null;
        }
    }

    
    public function mount($id)
    {
        $this->orders = Order::with(['items.measurements','files'])->findOrFail($id); // Fetch the order by ID
        
        if ($this->orders->customer_id) {
            $this->customerType = 'existing';
        } else {
            $this->customerType = 'new';
        }
        
        if ($this->orders) {
            foreach ($this->orders->files as $file) {

                if ($file->file_type == 'customer_image') {
        
                    $paths = explode(',', $file->file_path);
                    $this->customer_image = $paths;
        
                }
        
                if ($file->file_type == 'bill_book_copy') {
        
                    $paths = explode(',', $file->file_path);
                    $this->physical_order_bill_book = $paths;
        
                }
        
                if ($file->file_type == 'verified_video') {
        
                    $paths = explode(',', $file->file_path);
                    $this->verified_video = $paths;
        
                }
        
                if ($file->file_type == 'verified_audio') {
        
                    $paths = explode(',', $file->file_path);
                    $this->verified_audio = $paths;
        
                }
        
            }
          
            $this->order_number = $this->orders->order_number;
            $this->customer_id = $this->orders->customer_id;
            $this->name = $this->orders->customer_name;
            $this->email = $this->orders->customer_email;
            $this->dob = optional($this->orders->customer)->dob;
            $this->billing_address = $this->orders->billing_address;
            $this->air_mail = (int)$this->orders->air_mail;
            $this->phone = optional($this->orders->customer)->phone;
            $this->alternative_phone_number_1 = optional($this->orders->customer)->alternative_phone_number_1;
            $this->alternative_phone_number_2 = optional($this->orders->customer)->alternative_phone_number_2;
            $this->countries = Country::where('status',1)->get();
            $this->phone_code =  optional($this->orders->customer)->country_code_phone;
            $this->selectedCountryWhatsapp =  optional($this->orders->customer)->country_code_whatsapp;
            $this->alt_phone_code_1 =  optional($this->orders->customer)->country_code_alt_1;
            $this->alt_phone_code_2 =  optional($this->orders->customer)->country_code_alt_2;

            // Set mobile lengths based on selected countries
            if($this->orders->customer){
                 $this->isWhatsappPhone = UserWhatsapp::where('user_id',$this->orders->customer->id)->where('whatsapp_number',$this->phone)->exists();
                 $this->isWhatsappAlt1 = UserWhatsapp::where('user_id',$this->orders->customer->id)->where('whatsapp_number',$this->alternative_phone_number_1)->exists();
                 $this->isWhatsappAlt2 = UserWhatsapp::where('user_id',$this->orders->customer->id)->where('whatsapp_number',$this->alternative_phone_number_2)->exists();
            }else{
                $this->isWhatsappPhone = false;
                $this->isWhatsappAlt1 = false;
            }

            $this->catalogues = Catalogue::with('catalogueTitle')->get()->toArray();

            $this->items = $this->orders->items->map(function ($item) {
                $selected_titles = OrderMeasurement::where('order_item_id', $item->id)->pluck('measurement_name')->toArray();
                $selected_values = OrderMeasurement::where('order_item_id', $item->id)->pluck('measurement_value')->toArray();
                $fabrics = Fabric::join('product_fabrics', 'product_fabrics.fabric_id', '=', 'fabrics.id')
                                    ->where('product_fabrics.product_id', $item->product_id)
                                    ->select('fabrics.id', 'fabrics.title')
                                    ->get();

                // Get the selected fabric object if exists
                $selectedFabric = collect($fabrics)->firstWhere('id', $item->fabrics);

                // Map measurements with selected values
                $measurements = Measurement::where('product_id', $item->product_id)->orderBy('position','ASC')->get()
                    ->map(function ($measurement) use ($item, $selected_titles, $selected_values) {
                        $index = array_search($measurement->title, $selected_titles); // Check if title exists in selected titles
                        
                           // Fetch saved order measurement row
                            $savedMeasurement = OrderMeasurement::where(
                                'order_item_id',
                                $item->id
                            )
                            ->where('measurement_name', $measurement->title)
                            ->first();
                            
                        return [
                            'id' => $measurement->id,
                            'title' => $measurement->title,
                            'short_code' => $measurement->short_code,
                            'value' => $index !== false ? $selected_values[$index] : '', // Assign value if title is in selected titles
                            // NEW
                            'remarks' => $savedMeasurement->remarks ?? '',
                
                            // Auto open textarea if remarks exist
                            'show_remarks' => !empty($savedMeasurement->remarks),
                        ];
                });


                $pageItems = [];
                    if (!empty($item->catalogue_id) && !empty($item->cat_page_number)) {
                        $pageItems = CataloguePageItem::join('pages', 'catalogue_page_items.page_id', '=', 'pages.id')
                        ->where('catalogue_page_items.catalogue_id', $item->catalogue_id)
                        ->where('pages.page_number', $item->cat_page_number)
                        ->pluck('catalogue_page_items.catalog_item','catalogue_page_items.id')
                        ->toArray();
                    }
                return [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'searchproduct' => $item->product_name,
                    // 'air_mail'  => $item->air_mail,
                    'quantity'  => $item->quantity,
                    'price' => round($item->piece_price),
                    'remarks' => $item->remarks,
                    'item_status' => $item->status,
                    'selected_collection' => $item->collection,
                    'collection' => Collection::orderBy('title', 'ASC')->whereIn('id',[1,2])->get(),
                    'selected_category' => $item->category,
                    'categories' =>Category::orderBy('title', 'ASC')->where('collection_id', $item->collection)->get(),
                    'searchTerm' => optional($selectedFabric)->title, // Set default search value
                    'searchResults' => [],

                    'selected_fabric' => $item->fabrics,
                    'fabrics' => $fabrics,
                    'searchTerm' => optional($selectedFabric)->title ?? '',

                    'searchResults' => [],
                    'selected_measurements_title' => $selected_titles,
                    'selected_measurements_value' => $selected_values,
                    'measurements' => $measurements,
                    'catalogues' => $item->collection == 1 ? $this->catalogues : [],
                    'selectedCatalogue' => $item->catalogue_id,
                    'page_number' => $item->cat_page_number,
                    'pageItems' => $pageItems,
                    'page_item' => $item->cat_page_item,
                    'expected_delivery_date' => $item->expected_delivery_date,
                    'fitting' => $item->collection == 1 ? $item->fittings : '',
                    'priority' => $item->priority_level ?? null,
                    'status'  =>  $item->status,
                    'tl_status' => $item->tl_status,
                    'admin_status' => $item->admin_status,
                    
                    // Jacket fields
                    'mens_hand_stitching' => $item->mens_hand_stitching,
                    'ladies_hand_stitching' => $item->ladies_hand_stitching,
                    'shoulder_type' => $item->shoulder_type,
                    'vents' => $item->vents,
                    'vents_required' => $item->vents_required,
                    'vents_count' => $item->vents_count,
                    
                    // Trouser fields
                    'fold_cuff_required' => $item->fold_cuff_required,
                    'fold_cuff_size' => $item->fold_cuff_size,
                    'pleats_required' => $item->pleats_required,
                    'pleats_count' => $item->pleats_count,
                    'back_pocket_required' => $item->back_pocket_required,
                    'back_pocket_count' => $item->back_pocket_count,
                    'adjustable_belt' => $item->adjustable_belt,
                    'suspender_button' => $item->suspender_button,
                    'trouser_position' => $item->trouser_position,
                    
                    // Shirt fields
                    'sleeves'      => $item->sleeves,
                    'collar'       => $item->collar,
                    'collar_style' => $item->collar_style,
                    'pocket'       => $item->pocket,
                    'cuffs'        => $item->cuffs,
                    'cuff_style'   => $item->cuff_style,
                    'client_name_required' => $item->client_name_required,
                    'client_name_place'   => $item->client_name_place,
                    'client_name_options'   => $item->client_name_options,
                ];
            })->toArray();

        }
      
        $this->billing_address  = $this->orders->billing_address ?? '';
        $this->billing_landmark = $this->orders->billing_landmark ?? '';
        $this->billing_city     = $this->orders->billing_city ?? '';
        $this->billing_state    = $this->orders->billing_state ?? '';
        $this->billing_country  = $this->orders->billing_country ?? '';
        $this->billing_pin      = $this->orders->billing_pin ?? '';


        $this->Business_type = BusinessType::all();
        $this->selectedCountryId = optional($this->orders->customer)->country_id;
        $this->search = Country::where('id',optional($this->orders->customer)->country_id)->pluck('title');


        // $this->selectedBusinessType = optional($this->orders->customer)->business_type;
         $this->selectedBusinessType = \App\Models\BusinessType::where('title', 'TEXTILES')->value('id');
        $this->customer_id = $this->orders->customer_id;
        $this->prefix = $this->orders->prefix;
        $this->name = $this->orders->customer_name;
        $this->company_name = optional($this->orders->customer)->company_name;
        $this->employee_rank = optional($this->orders->customer)->employee_rank;
        $this->email = $this->orders->customer_email;
        $this->dob = optional($this->orders->customer)->dob;

        $this->customers = User::where('user_type', 1)->where('status', 1)->orderBy('name', 'ASC')->get();
        $this->categories = Category::where('status', 1)->orderBy('title', 'ASC')->get();
        $this->collections = Collection::orderBy('title', 'ASC')->whereIn('id',[1,2])->get();

        $this->paid_amount = $this->orders->paid_amount;
        $this->billing_amount =  $this->orders->total_amount;
        $this->remaining_amount =  $this->orders->remaining_amount;
        $this->payment_mode = $this->orders->payment_mode;
        $this->salesmanBill = SalesmanBilling::where('salesman_id',auth()->guard('admin')->user()->id)->first();


        foreach ($this->items as $index => $item) {
            $this->extra_measurement[$index] = Helper::ExtraRequiredMeasurement(trim($item['searchproduct'] ?? $item['product_name'] ?? ''));

            if(!empty($item['page_item'])){
                $this->catalogue_page_item[$index] = $item['page_item'];
            }   

             

            $this->existingImages[$index] = OrderItemCatalogueImage::where('order_item_id', $item['order_item_id'])->pluck('image_path')->toArray();
            $this->existingVideos[$index] = OrderItemVoiceMessage::where('order_item_id', $item['order_item_id'])->pluck('voices_path')->toArray();

            $this->items[$index]['copy_previous_measurements'] = false; // Ensure checkbox is not selected

             // Load categories for the selected collection
            $categories = Category::orderBy('title', 'ASC')
                ->where('collection_id', $item['selected_collection'])
                ->where('status', 1)
                ->get();

            $this->items[$index]['categories'] = $categories;

            // Auto-select ACCESSORIES for collection 2 if no category selected yet
            if ($item['selected_collection'] == 2 && empty($item['selected_category'])) {
                $accessories = $categories->firstWhere('title', 'ACCESSORIES');
                if ($accessories) {
                    $this->items[$index]['selected_category'] = $accessories->id;
                }
            }
        }
    }
    
    // ─── UNIFIED VIEW HANDLERS FOR EDIT LAYER ──────────────────────────────────
    public function toggleUnifiedMeasurementView()
    {
        $this->isUnifiedViewActive = !$this->isUnifiedViewActive;

        if (!$this->isUnifiedViewActive) {
            $this->reset('unifiedMeasurements');
            return;
        }

        $aggregated = [];

        foreach ($this->items as $itemIndex => $item) {
            if (($item['selected_collection'] ?? null) != 1 || empty($item['measurements'])) {
                continue;
            }

            foreach ($item['measurements'] as $measurementKey => $mDetails) {
                $shortCode = trim($mDetails['short_code'] ?? '');
                $title     = trim($mDetails['title'] ?? '');
                
                $uniqueKey = preg_replace('/[^A-Za-z0-9]/', '_', trim($shortCode . '_' . $title));

                if (!isset($aggregated[$uniqueKey])) {
                    $aggregated[$uniqueKey] = [
                        'title'        => $title,
                        'short_code'   => $shortCode,
                        'value'        => $mDetails['value'] ?? '',
                        'remarks'      => $mDetails['remarks'] ?? '',
                        'show_remarks' => !empty($mDetails['show_remarks']),
                        'mappings'     => []
                    ];
                }

                $aggregated[$uniqueKey]['mappings'][] = [
                    'item_index'      => $itemIndex,
                    'measurement_key' => $measurementKey
                ];
            }
        }

        $masterOrderOrder = [
            'FRT', 'H.BST', 'BST', 'B.BST', 'CST', 'AF CST', 'STM', 'WST', 'HPS', 'HPS (TRS)', 
            'CRS', 'SLD (JKT)', 'BTB', 'SLD-BST', 'SLD-F.WST', 'SLD-B.WST', 'SLV (JKT)', 
            'J/L', 'W/C LTH', 'MSL', 'WRT', 'T/L', 'INS (B)', 'INS (S)', 'CRT', 'THG', 'KNE', 'BTM', 'COL'
        ];

        $sortedAggregated = [];
        foreach ($masterOrderOrder as $code) {
            foreach ($aggregated as $uKey => $data) {
                if ($data['short_code'] === $code) {
                    $sortedAggregated[$uKey] = $data;
                    unset($aggregated[$uKey]);
                }
            }
        }

        foreach ($aggregated as $remainingKey => $data) {
            $sortedAggregated[$remainingKey] = $data;
        }

        $this->unifiedMeasurements = $sortedAggregated;
    }

    public function updatedUnifiedMeasurements($value, $key)
    {
        $lastDotPosition = strrpos($key, '.');
        if ($lastDotPosition === false) return;
    
        $uniqueKey = substr($key, 0, $lastDotPosition); 
        $field     = substr($key, $lastDotPosition + 1); 
    
        if (isset($this->unifiedMeasurements[$uniqueKey])) {
            // Strip away the collection proxies entirely
            $itemsArray = json_decode(json_encode($this->items), true);
    
            foreach ($this->unifiedMeasurements[$uniqueKey]['mappings'] as $mapping) {
                $iIdx = $mapping['item_index'];
                $mKey = $mapping['measurement_key'];
    
              if (isset($itemsArray[$iIdx]['measurements'][$mKey])) {
                    $itemsArray[$iIdx]['measurements'][$mKey][$field] = $value;
                    $this->unifiedMeasurements[$uniqueKey][$field] = $value;
                    
                    // Real-time error synchronization logic
                    if ($field === 'value') {
                        if (empty($value)) {
                            $this->addError("unifiedMeasurements.{$uniqueKey}.value", "The " . ($this->unifiedMeasurements[$uniqueKey]['title'] ?? 'Measurement') . " value is required.");
                        } else {
                            $this->resetErrorBag("unifiedMeasurements.{$uniqueKey}.value");
                        }
                    }
                }
                
            }
            $this->items = $itemsArray;
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $customAttributes = [];
    
            if ($this->isUnifiedViewActive) {
                foreach ($this->unifiedMeasurements as $uKey => $uField) {
                    $fieldName = !empty($uField['title']) ? $uField['title'] : $uField['short_code'];
                    // Perform required value verification loops on submission inside Unified Panel state
                    if (empty($uField['value']) && $uField['value'] !== '0') {
                        $validator->errors()->add("unifiedMeasurements.{$uKey}.value", "The {$fieldName} measurement field is mandatory.");
                    }
                    $customAttributes["unifiedMeasurements.{$uKey}.value"] = "{$fieldName} Value";
                    $customAttributes["unifiedMeasurements.{$uKey}.remarks"] = "{$fieldName} Remarks";
                }
            } else {
                foreach ($this->items as $index => $item) {
                    $measurements = $item['measurements'] ?? [];
                    $itemNum = $index + 1;
                    foreach ($measurements as $mKey => $measurement) {
                        $mName = !empty($measurement['title']) ? $measurement['title'] : ($measurement['short_code'] ?? 'Measurement');
                        $customAttributes["items.{$index}.measurements.{$mKey}.value"] = "(Item #{$itemNum}) {$mName} Value";
                        $customAttributes["items.{$index}.measurements.{$mKey}.remarks"] = "(Item #{$itemNum}) {$mName} Remarks";
                    }
                }
            }
    
            $validator->setAttributeNames(array_merge($validator->customAttributes, $customAttributes));
        });
    }

 // ─── UNIFIED VIEW HANDLERS FOR EDIT LAYER END──────────────────────────────────
 
    public function updatedItems($value, $key)
    {
        [$index, $field] = explode('.', $key);

        if ($field === 'selected_collection' && $this->items[$index]['selected_collection'] == 1) {
            $this->items[$index]['quantity'] = 1;
        }
    }

    public function GetCountryDetails($mobileLength, $field)
    {
        switch($field){
            case 'phone':
                $this->mobileLengthPhone = $mobileLength;
                break;

            case 'whatsapp':
                $this->mobileLengthWhatsapp = $mobileLength;
                break;

            case 'alt_phone_1':
                $this->mobileLengthAlt1 = $mobileLength;
                break;

            case 'alt_phone_2':
                $this->mobileLengthAlt2 = $mobileLength;
                break;
        }
    }


    public function addItem()
    {
        $this->items = array_values($this->items);
        $this->items[] = [

            'selected_collection' => '',
            'selected_category' => '',
            'collection' =>  Collection::orderBy('title', 'ASC')->whereIn('id',[1,2])->get(),
            'categories' => [],
            'searchproduct' => '',
            'selected_fabric' => null,
            'measurements' => [],
            'products' => [],
            'product_id' => null,
            'price' => '', // Ensure price is initialized to an empty string, not null.
            'fabrics' => [],
            // 'selected_fabric' => '',
            'catalogues' => [],
            'selectedCatalogue' => '',
            'page_number' => '',
            'pageItems' => [],
            'page_item' => null,
            'fold_cuff_required' => 'No',
            'pleats_required' => 'No',
            'back_pocket_required' => '2',
            'mens_hand_stitching' => 'No',
        ];
        // Ensure catalogues and max pages are initialized

    }

    public function FindCountry($term){
        $this->search = $term;
        if(!empty($this->search)){
            $this->filteredCountries = Country::where('title', 'LIKE', '%' . $this->search . '%')->get();
        }else{
            $this->filteredCountries = [];
        }
    }

    public function searchFabrics($index)
    {
        // Ensure product_id exists for the given index
        if (!isset($this->items[$index]['product_id'])) {
            return;
        }

        $productId = $this->items[$index]['product_id'];

        // Ensure searchTerm exists for this index
        $searchTerm = $this->items[$index]['searchTerm'] ?? '';

        if (!empty($searchTerm)) {
            $this->items[$index]['searchResults'] = Fabric::join('product_fabrics', 'fabrics.id', '=', 'product_fabrics.fabric_id')
                ->leftJoin('stock_fabrics', 'fabrics.id', '=', 'stock_fabrics.fabric_id')
                ->where('product_fabrics.product_id', $productId)
                ->where('fabrics.status', 1)
                ->where('fabrics.title', 'LIKE', "%{$searchTerm}%")
                ->select('fabrics.id', 'fabrics.title', \DB::raw('COALESCE(SUM(stock_fabrics.qty_in_meter),0) as available_stock'))
                ->groupBy('fabrics.id', 'fabrics.title')
                ->limit(10)
                ->get();
                // dd($this->items[$index]['searchResults']);
        } else {
            $this->items[$index]['searchResults'] = [];
        }
    }

    public function selectFabric($fabricId, $index)
    {
        // Get the selected fabric details
        $fabric = Fabric::find($fabricId);

        if (!$fabric) {
            return;
        }

        // Set the exact selected fabric name
        $this->items[$index]['searchTerm'] = $fabric->title;
        $this->items[$index]['selected_fabric'] = $fabric->id;
        
         // Check stock immediately
        $stock = StockFabric::where('fabric_id', $fabricId)->value('qty_in_meter');
        if (is_null($stock) || $stock <= 0) {
            $this->addError("items.$index.searchTerm", "Chosen fabric is out of stock.");
        }else{
             $this->resetErrorBag("items.$index.searchTerm");
        }
        
        // Clear search results to hide the dropdown after selection
        $this->items[$index]['searchResults'] = [];
    }


    // public function rules()
    // {
    //     $auth = Auth::guard('admin')->user();
    //     $hasGarment = collect($this->items)->contains('selected_collection',1);
    //      // Check if customer already has image (edit case)
    //     $hasOldImage = !empty($this->old_customer_image);

    //     $rules = [
    //         'items' => 'required|min:1',
    //         'items.*.selected_collection' => 'required',
    //         'items.*.searchTerm' => 'required_if:items.*.selected_collection,1',
    //         'items.*.selected_category' => 'required',
    //         'items.*.searchproduct' => 'required',
    //         'items.*.price' => 'required|numeric|min:1',
    //         'items.*.quantity' => 'required|numeric|min:1',
    //         'items.*.fitting' => 'required_if:items.*.selected_collection,1',
    //         'items.*.expected_delivery_date' => 'required',
    //         'items.*.item_status' => 'required',
                
    //         'customer_image' => $hasGarment
    //             ? ($hasOldImage
    //                 ? 'nullable'
    //                 : 'required')
    //             : 'nullable',
                
    //          // Physical Bill Book (NEW RULE)
    //         'physical_order_bill_book_new' => [
    //             'nullable',
    //             'array',
    //             Rule::requiredIf(empty($this->physical_order_bill_book)),
    //         ],
    //     ];
          
            
    //          foreach ($this->items as $index => $item) {
    //             if (isset($item['selectedCatalogue']) &&
    //                 isset($this->catalogues[$index][$item['selectedCatalogue']]) &&
    //                 $this->catalogues[$index][$item['selectedCatalogue']] === 'No Catalogue Images') {
    
    //                 // Make selectedCatalogue,page_number optional
    //                 $rules["items.$index.selectedCatalogue"] = 'nullable';
    //                 $rules["items.$index.page_number"] = 'nullable';
    //             } else {
    //                 // Otherwise required if collection = 1
    //                 $rules["items.$index.selectedCatalogue"] = 'required_if:items.*.collection,1';
    //                 $rules["items.$index.page_number"] = 'required_if:items.*.collection,1';
    //             }
    //             //  $rules["items.$index.fitting"] = 'required';
    //         }
    //     //  Add dynamic rules based on extra measurement per index
    //     foreach ($this->items as $index => $item) {
    //         $extra = $this->extra_measurement[$index] ?? [];
            
    //         /* ================= MEN JACKET ================= */
    //         if (in_array('mens_jacket_suit',$extra)) {
    //             $rules["items.$index.vents"] = 'required';
    //             $rules["items.$index.shoulder_type"] = 'required';
    //             $rules["items.$index.mens_hand_stitching"] = 'required';
    //         }
            
    //          /* ================= LADIES JACKET ================= */
    //         if (in_array('ladies_jacket_suit',$extra)) {
    //             $rules["items.$index.shoulder_type"] = 'required';
    //             $rules["items.$index.ladies_hand_stitching"] = 'required';
    //             $rules["items.$index.vents_required"] = 'required';
    //             $rules["items.$index.vents_count"]    = 'required_if:items.'.$index.'.vents_required,Yes|nullable|integer|min:1';
    //         }

    //         if (in_array('trouser',$extra)) {
    //             $rules["items.$index.fold_cuff_required"]   = 'required';
    //             $rules["items.$index.fold_cuff_size"]       = 'required_if:items.'.$index.'.fold_cuff_required,Customized|nullable|numeric|min:1';
                
    //             $rules["items.$index.pleats_required"]      = 'required';
    //             // $rules["items.$index.pleats_count"]         = 'required_if:items.'.$index.'.pleats_required,Yes|nullable|integer|min:1';
                
    //             $rules["items.$index.back_pocket_required"] = 'required';
    //             // $rules["items.$index.back_pocket_count"]    = 'required_if:items.'.$index.'.back_pocket_required,Yes|nullable|integer|min:1';
                
    //             $rules["items.$index.adjustable_belt"]      = 'required';
    //             $rules["items.$index.suspender_button"]     = 'required';
    //             $rules["items.$index.trouser_position"]     = 'required';
    //         }
    //         if (in_array('shirt',$extra)) {
    //             $rules["items.$index.sleeves"] = 'required';
    //             $rules["items.$index.collar"]  = 'required';
    //             $rules["items.$index.pocket"]  = 'required';
    //             $rules["items.$index.cuffs"]   = 'required';
    //             $rules["items.$index.collar_style"] = 'required_if:items.'.$index.'.collar,Other';
    //             $rules["items.$index.cuff_style"]   = 'required_if:items.'.$index.'.cuffs,Other';
    //         }
    //         if (in_array('ladies_jacket_suit',$extra) || in_array('shirt',$extra) || in_array('mens_jacket_suit',$extra)) {
    //             $rules["items.$index.client_name_required"] = 'required';
    //             $rules["items.$index.client_name_place"] = 'required_if:items.'.$index.'.client_name_required,Yes';
    //         }
    //     }

    //     if (in_array($auth->designation, [1, 4])) {
    //         $rules['items.*.priority'] = 'required';
    //     }

    //     foreach ($this->items as $index => $item) {
    //             if (isset($item['selected_collection']) && $item['selected_collection'] == 1) {
            
    //                 $rules["items.$index.searchTerm"][] = function ($attribute, $value, $fail) use ($item) {
    //                     $stock = StockFabric::where('fabric_id', $item['selected_fabric'])
    //                                 ->value('qty_in_meter');
            
    //                     if (is_null($stock) || $stock <= 0) {
    //                         $fail('Chosen fabric is out of stock.');
    //                     }
    //                 };
    //             }
    //         }
            
    //     // Conditional Validation for Measurement Values and Open Remarks Fields
    //     if ($this->isUnifiedViewActive) {
    //         foreach ($this->unifiedMeasurements as $uKey => $uField) {
    //             $rules["unifiedMeasurements.{$uKey}.value"] = 'required';
                
    //             // If the remark section chat bubble icon is expanded/active -> Make remarks required
    //             if (!empty($uField['show_remarks'])) {
    //                 $rules["unifiedMeasurements.{$uKey}.remarks"] = 'required|string|min:1';
    //             } else {
    //                 $rules["unifiedMeasurements.{$uKey}.remarks"] = 'nullable';
    //             }
    //         }
    //     } else {
    //         foreach ($this->items as $index => $item) {
    //             if (($item['selected_collection'] ?? null) == 1 && isset($item['measurements'])) {
    //                 foreach ($item['measurements'] as $mKey => $measurement) {
    //                     $rules["items.{$index}.measurements.{$mKey}.value"] = 'required';
                        
    //                     // Standard Row Mode: If remark section is expanded -> Make it required
    //                     if (!empty($measurement['show_remarks'])) {
    //                         $rules["items.{$index}.measurements.{$mKey}.remarks"] = 'required|string|min:1';
    //                     } else {
    //                         $rules["items.{$index}.measurements.{$mKey}.remarks"] = 'nullable';
    //                     }
    //                 }
    //             }
    //         }
    //     }
            
            
         
            
    //     return $rules;
    // }
    
    public function rules()
    {
    $auth = Auth::guard('admin')->user();
    $hasGarment = collect($this->items)->contains('selected_collection',1);
    $hasOldImage = !empty($this->old_customer_image);

    $rules = [
        'items' => 'required|min:1',
        'items.*.selected_collection' => 'required',
        'items.*.selected_category'   => 'required',
        'items.*.searchproduct'       => 'required',
        'items.*.price'               => 'required|numeric|min:1',
        'items.*.quantity'            => 'required|numeric|min:1',
        'items.*.expected_delivery_date' => 'required',
        'items.*.item_status'         => 'required',
        
        'customer_image' => $hasGarment ? ($hasOldImage ? 'nullable' : 'required') : 'nullable',
        
        'physical_order_bill_book_new' => [
            'nullable',
            'array',
            Rule::requiredIf(empty($this->physical_order_bill_book)),
        ],
    ];

    // ==================== MAIN FIELD VALIDATIONS ====================
    foreach ($this->items as $index => $item) {
        $collection = $item['selected_collection'] ?? null;

        // FABRIC (Garment only)
        if ($collection == 1) {
            $rules["items.$index.searchTerm"] = 'required';
            $rules["items.$index.fitting"]    = 'required';           // ← Fixed Fitting
        }

        // CATALOGUE + PAGE NUMBER
        $isNoCatalogue = false;
        if (isset($item['selectedCatalogue']) && !empty($this->catalogues)) {
            $cat = collect($this->catalogues)->firstWhere('id', $item['selectedCatalogue']);
            if ($cat && ($cat['catalogue_title']['title'] ?? '') === 'No Catalogue Images') {
                $isNoCatalogue = true;
            }
        }

        if ($collection == 1) {
            if ($isNoCatalogue) {
                $rules["items.$index.selectedCatalogue"] = 'nullable';
                $rules["items.$index.page_number"]       = 'nullable';
            } else {
                $rules["items.$index.selectedCatalogue"] = 'required';
                $rules["items.$index.page_number"]       = 'required|integer|min:1';
            }
        }
    }

    // ==================== EXTRA MEASUREMENT RULES ====================
    foreach ($this->items as $index => $item) {
        $extra = $this->extra_measurement[$index] ?? [];
        
        if (in_array('mens_jacket_suit', $extra)) {
            $rules["items.$index.vents"] = 'required';
            $rules["items.$index.shoulder_type"] = 'required';
            $rules["items.$index.mens_hand_stitching"] = 'required';
        }
        
        if (in_array('ladies_jacket_suit', $extra)) {
            $rules["items.$index.shoulder_type"] = 'required';
            $rules["items.$index.ladies_hand_stitching"] = 'required';
            $rules["items.$index.vents_required"] = 'required';
            $rules["items.$index.vents_count"] = 'required_if:items.'.$index.'.vents_required,Yes|nullable|integer|min:1';
        }

        if (in_array('trouser', $extra)) {
            $rules["items.$index.fold_cuff_required"]   = 'required';
            $rules["items.$index.pleats_required"]      = 'required';
            $rules["items.$index.back_pocket_required"] = 'required';
            $rules["items.$index.adjustable_belt"]      = 'required';
            $rules["items.$index.suspender_button"]     = 'required';
            $rules["items.$index.trouser_position"]     = 'required';
        }

        if (in_array('shirt', $extra)) {
            $rules["items.$index.sleeves"] = 'required';
            $rules["items.$index.collar"]  = 'required';
            $rules["items.$index.pocket"]  = 'required';
            $rules["items.$index.cuffs"]   = 'required';
        }

        // if (in_array('ladies_jacket_suit', $extra) || in_array('shirt', $extra) || in_array('mens_jacket_suit', $extra)) {
        //     $rules["items.$index.client_name_required"] = 'required';
        //     $rules["items.$index.client_name_place"] = 'required_if:items.'.$index.'.client_name_required,Yes';
        //     $rules["items.$index.client_name_options"] = 'required_if:items.'.$index.'.client_name_required,Yes';
        // }
        
        // CLIENT NAME (common)
        if (
            in_array('ladies_jacket_suit', $extra) ||
            in_array('shirt', $extra) ||
            in_array('mens_jacket_suit', $extra)
        ) {
            $rules["items.$index.client_name_required"] = 'required';
        
            $rules["items.$index.client_name_place"] =
                'required_if:items.'.$index.'.client_name_required,Yes';
        }
        
        
        // CLIENT NAME OPTIONS (only shirt)
        if (in_array('shirt', $extra)) {
        
            $rules["items.$index.client_name_options"] =
                'required_if:items.'.$index.'.client_name_required,Yes';
        }
    }

    if (in_array($auth->designation, [1, 4])) {
        $rules['items.*.priority'] = 'required';
    }

    return $rules;
}

    protected function messages()
    {
        return [
            'items.required' => 'Please add at least one item to the order.',
            'items.*.selected_collection.required'    => 'Please select a collection for the item.',
            'items.*.searchTerm.required'    => 'Please select a fabric for the item.',
            'items.*.selected_category.required'      => 'Please select a category for the item.',
            'items.*.searchproduct.required'          => 'Please select a product for the item.',
            'items.*.price.required'                  => 'Please enter a price for the item.',
            'items.*.selectedCatalogue.required'   => 'Please select a catalogue for the item.',
            'items.*.page_number.required'         => 'Please select a page for the item.',
            'items.*.quantity.required'               => 'Please select a quantity for the item.',
            'items.*.fitting.required'             => 'Please select fittings for the item.',
            'items.*.priority.required'               => 'Please select a priority for the item.',
            'items.*.expected_delivery_date.required' => 'Please select expected delivery date for the item.',
            'items.*.item_status.required'            => 'Please select a status for the item.',

            //  Extra measurement messages
            'items.*.shoulder_type.required'          => 'Please select shoulder type.',
            'items.*.mens_hand_stitching.required'    => 'Please select a hand stitching option for the men’s jacket.',
            'items.*.ladies_hand_stitching.required'  => 'Please select a hand stitching option for the ladies’ jacket.',
            'items.*.vents.required'                  => 'Please select vents option for mens suit/jacket.',
            'items.*.vents_required.required'         => 'Please specify if vents are required for ladies suit/jacket.',
            'items.*.vents_count.required_if'         => 'Please specify how many vents for ladies suit/jacket.',
            'items.*.fold_cuff_required.required'     => 'Please specify if fold cuffs are required for the trouser.',
            'items.*.fold_cuff_size.required_if'      => 'Please enter the cuff size if fold cuffs are required.',
            'items.*.pleats_required.required'        => 'Please specify if pleats are required for the trouser.',
            // 'items.*.pleats_count.required_if'        => 'Please specify how many pleats for the trouser.',
            'items.*.back_pocket_required.required'   => 'Please specify if back pockets are required for the trouser.',
            // 'items.*.back_pocket_count.required_if'   => 'Please specify how many back pockets for the trouser.',
            'items.*.adjustable_belt.required'        => 'Please specify if an adjustable belt is required.',
            'items.*.suspender_button.required'       => 'Please specify if suspender buttons are required.',
            'items.*.trouser_position.required'       => 'Please select the trouser position.',
             'items.*.measurements.*.remarks.required' => 'Please enter a remark.',

            'items.*.sleeves.required'      => 'Please select sleeves (L/S or H/S).',
            'items.*.collar.required'       => 'Please select a collar option.',
            'items.*.collar_style.required_if' => 'Please specify the collar style.',
            'items.*.pocket.required'       => 'Please select pocket option.',
            'items.*.cuffs.required'        => 'Please select cuff option.',
            'items.*.cuff_style.required_if'=> 'Please specify the cuff style.',
            'items.*.client_name_required.required' => 'Please specify if client name is required on the item.',
            'items.*.client_name_place.required_if' => 'Please specify where the client name should be placed on the item.',
            'items.*.client_name_options.required_if' => 'Please specify where the client options should be placed on the item.',
        ];
    }
     public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function validateSingle($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }
    public function removeItem($index)
    {
        $itemId = $this->items[$index]['order_item_id'] ?? null;
        if ($itemId) {
            // Actually remove from the DB (or set a 'deleted' flag)
            $orderItem = OrderItem::find($itemId);
            if ($orderItem) {
                $orderItem->delete();
            }
        }
        unset($this->items[$index]);
        unset($this->extra_measurement[$index]);
        
        $this->items = array_values($this->items);
        // $this->extra_measurement = array_values($this->extra_measurement); 
        
        // Sync unified preview grid maps references matching indices layout removal changes
            if (!empty($this->unifiedMeasurements)) {
                foreach ($this->unifiedMeasurements as $uKey => $data) {
                    $updatedMappings = [];
                    foreach ($data['mappings'] as $mapping) {
                        if ($mapping['item_index'] < $index) {
                            $updatedMappings[] = $mapping;
                        } elseif ($mapping['item_index'] > $index) {
                            // Shift index down safely since array keys were re-indexed above
                            $mapping['item_index'] = $mapping['item_index'] - 1;
                            $updatedMappings[] = $mapping;
                        }
                    }
                    if (empty($updatedMappings)) {
                        unset($this->unifiedMeasurements[$uKey]);
                    } else {
                        $this->unifiedMeasurements[$uKey]['mappings'] = $updatedMappings;
                    }
                }
            }
        
        $this->updateBillingAmount();  // Update billing amount after checking price
    }

    public function updateBillingAmount()
    {
        // Recalculate the total billing amount
        $itemTotal = array_sum(array_column($this->items, 'price'));
        $airMail = floatval($this->air_mail);
        $this->billing_amount = $airMail > 0 ? ($itemTotal + $airMail) : $itemTotal;
        $this->paid_amount = $this->billing_amount;
        $this->GetRemainingAmount($this->paid_amount);
        return;
    }

    public function GetRemainingAmount($paid_amount)
    {

        // Ensure the values are numeric before performing subtraction
        $billingAmount =  floatval($this->billing_amount);
        $paidAmount = floatval($paid_amount);
        $paidAmount = ltrim($paidAmount, '0');
        if ($billingAmount > 0) {
            if(empty($paid_amount)){
                $this->paid_amount = 0;
                $this->remaining_amount = $billingAmount;
                return;
            }
            $this->paid_amount = $paidAmount;
            $this->remaining_amount = $billingAmount - $this->paid_amount;

            // Check if the remaining amount is negative
            if ($this->remaining_amount < 0) {
                $this->remaining_amount = 0;
                $this->paid_amount = $this->billing_amount;
                session()->flash('errorAmount', 'The paid amount exceeds the billing amount.');
            }
        } else {
            $this->paid_amount = 0;

            session()->flash('errorAmount', 'ðŸš¨ Please add item amount first.');
        }
    }

    public function GetCategory($value,$index)
    {
        // Store the currently selected catalogue before resetting
        $previousCatalogue = $this->items[$index]['selectedCatalogue'] ?? null;

        // Reset products, and product_id for the selected item
        $this->items[$index]['product_id'] = null;
        $this->items[$index]['measurements'] = [];
        $this->items[$index]['fabrics'] = [];

        // Fetch categories and products based on the selected collection
        $this->items[$index]['categories'] = Category::orderBy('title', 'ASC')->where('collection_id', $value)->get();
        
        $categories = $this->items[$index]['categories'];
         // Auto-select ACCESSORIES if collection = 2
            if ($value == 2) {
                $accessories = $categories->firstWhere('title', 'ACCESSORIES');
                if ($accessories) {
                    $this->items[$index]['selected_category'] = $accessories['id'];
                } else {
                    $this->items[$index]['selected_category'] = null;
                }
            } else {
                // Reset category if collection != 2
                $this->items[$index]['selected_category'] = null;
            }

        if ($value == 1) {
            $catalogues = Catalogue::with('catalogueTitle')->get();

            // Store catalogues inside items array
            $this->items[$index]['catalogues'] = $catalogues->map(function ($catalogue) {
                return [
                    'id' => $catalogue->catalogue_title_id,
                    'catalogue_title' =>[ 'title' => $catalogue->catalogueTitle->title ],
                    'page_number' => $catalogue->page_number,
                ];
            })->toArray();


            // Fetch max page numbers per catalogue
            $this->maxPages[$index] = [];
            foreach ($catalogues as $catalogue) {
                $this->maxPages[$index][$catalogue->catalogue_title_id] = $catalogue->page_number;
            }

            if ($previousCatalogue) {
                $selectedCatalogue = collect($this->items[$index]['catalogues'])->firstWhere('id', $previousCatalogue);
                if ($selectedCatalogue) {
                    $this->items[$index]['selectedCatalogue'] = $selectedCatalogue['id'];
                }
            }

        } else {
            $this->items[$index]['catalogues'] = [];
            $this->maxPages[$index] = [];
        }
    }


    public function SelectedCatalogue($catalogueId, $index)
    {
        $this->items[$index]['page_number'] = null; // Reset page number
        $this->items[$index]['page_item'] = null; // Reset page number
        //  if (!isset($this->maxPages[$index])) {
           $this->maxPages[$index] = []; // Reset max page number
        // }
        // Fetch max page number from database
        $maxPage = Catalogue::where('id', $catalogueId)->value('page_number');

        if ($maxPage) {
            $this->maxPages[$index][$catalogueId] = $maxPage;
        }
    }

  
    
    public function validatePageNumber($value, $index)
{
    if (
        !isset($this->items[$index]['page_number']) ||
        !isset($this->items[$index]['selectedCatalogue'])
    ) {
        return;
    }

    $pageNumber = (int) $this->items[$index]['page_number'];
    $selectedCatalogue = $this->items[$index]['selectedCatalogue'];

    // Fetch page items
    $this->items[$index]['pageItems'] = CataloguePageItem::join(
            'pages',
            'catalogue_page_items.page_id',
            '=',
            'pages.id'
        )
        ->where('catalogue_page_items.catalogue_id', $selectedCatalogue)
        ->where('pages.page_number', $pageNumber)
        ->pluck('catalogue_page_items.catalog_item')
        ->toArray();

    // ---------- PAGE NUMBER VALIDATION ----------
    $maxPage = $this->maxPages[$index][$selectedCatalogue] ?? null;

    if ($maxPage && ($pageNumber < 1 || $pageNumber > $maxPage)) {
        $this->addError(
            "items.$index.page_number",
            "Page number must be between 1 and $maxPage."
        );
    } else {
        $this->resetErrorBag("items.$index.page_number");
    }

    // ---------- PAGE ITEM HANDLING ----------
    if (!empty($this->items[$index]['pageItems'])) {

        // Reset invalid selection
        if (
            !in_array(
                $this->items[$index]['page_item'] ?? null,
                $this->items[$index]['pageItems']
            )
        ) {
            $this->items[$index]['page_item'] = null;
        }

        //  REQUIRED VALIDATION FOR PAGE ITEM
        if (empty($this->items[$index]['page_item'])) {
            $this->addError(
                "items.$index.page_item",
                "Please select a page item."
            );
        } else {
            $this->resetErrorBag("items.$index.page_item");
        }

    } else {
        // No page items → clear selection & error
        $this->items[$index]['page_item'] = null;
        $this->resetErrorBag("items.$index.page_item");
    }
}

     private function copyExtraMeasurements(int $currentIndex, int $productId): void
    {
        // Walk backwards from currentIndex - 1 to find the nearest same product
        for ($i = $currentIndex - 1; $i >= 0; $i--) {
            if (isset($this->items[$i]['product_id']) && $this->items[$i]['product_id'] == $productId) {
    
                // Fields that belong to extra_measurement (not standard measurements)
                $extraFields = [
                    // Men/Ladies Jacket
                    'vents', 'vents_required', 'vents_count',
                    'client_name_required', 'client_name_place','client_name_options',
                    'shoulder_type','mens_hand_stitching','ladies_hand_stitching',
    
                    // Trouser
                    'fold_cuff_required', 'fold_cuff_size',
                    'pleats_required',
                    'back_pocket_required',
                    'adjustable_belt', 'suspender_button', 'trouser_position',
    
                    // Shirt
                    'sleeves', 'collar', 'collar_style',
                    'pocket', 'cuffs', 'cuff_style',
    
                    // Garment shared
                    'fitting', 'priority', 'item_status',
                    'remarks','expected_delivery_date','selectedCatalogue', 'page_number', 'page_item',
                ];
    
                foreach ($extraFields as $field) {
                    if (isset($this->items[$i][$field]) && $this->items[$i][$field] !== '' && $this->items[$i][$field] !== null) {
                        $this->items[$currentIndex][$field] = $this->items[$i][$field];
                    }
                }
    
                break; // Stop after first (nearest) match
            }
        }
    }


    public function selectProduct($index, $name, $id)
    {
        $this->items[$index]['searchproduct'] = $name;
        $this->items[$index]['product_id'] = $id;
        $this->items[$index]['products'] = [];
    
        $this->extra_measurement[$index] = Helper::ExtraRequiredMeasurement(trim($name));
    
        // Load raw measurements first
        $measurements = Measurement::where('product_id', $id)
            ->where('status', 1)
            ->orderBy('position','ASC')
            ->get();
    
        // Try to fetch customer's previous order measurement values for this product
        $previousValues = OrderMeasurement::whereHas('orderItem', function ($q) use ($id) {
                $q->where('product_id', $id)
                  ->whereHas('order', function ($qq) {
                      $qq->where('customer_id', $this->customer_id); // current order's customer
                  });
            })
            ->pluck('measurement_value', 'measurement_name')
            ->toArray();
    
        // Map with values (prefill automatically)
        $this->items[$index]['measurements'] = $measurements->map(function ($m) use ($previousValues) {
            return [
                'id' => $m->id,
                'title' => $m->title,
                'short_code' => $m->short_code,
                'value' => $previousValues[$m->title] ?? '' // prefill if exists
            ];
        })->toArray();
    
        // Load fabrics for the product
        $this->items[$index]['fabrics'] = Fabric::join('product_fabrics', 'fabrics.id', '=', 'product_fabrics.fabric_id')
            ->where('product_fabrics.product_id', $id)
            ->where('fabrics.status', 1)
            ->get(['fabrics.*']);
    
        // Auto-select collection if not already set
        $product = Product::find($id);
        if (empty($this->items[$index]['selected_collection'])) {
            $this->items[$index]['selected_collection'] = $product && $product->collection->isNotEmpty()
                ? $product->collection->first()->id
                : null;
        }
        $this->items[$index]['catalogues'] = $this->items[$index]['selected_collection'] == 1 ? $this->catalogues : [];
    
        // Error handling
        session()->forget('measurements_error.' . $index);
        if (empty($this->items[$index]['measurements'])) {
            session()->flash('measurements_error.' . $index, '🚨 Oops! Measurement data not added for this product.');
            return;
        }
        
         // Copy extra fields from nearest matching row above (same as create page)
        if ($index > 0) {
            $this->copyExtraMeasurements($index, $id);
        }
    
        //   If copy checkbox is already ticked → apply it immediately
            if (!empty($this->items[$index]['copy_previous_measurements'])) {
                $this->copyMeasurements($index);
            }
            
            // For Unified Measurement
            if ($this->isUnifiedViewActive) {
                $this->mergeProductIntoUnifiedView($index);
            }
    }

  protected function mergeProductIntoUnifiedView(int $itemIndex): void
    {
        $item = $this->items[$itemIndex];
        if (empty($item['measurements'])) return;
    
        $masterOrderOrder = [
            'FRT', 'H.BST', 'BST', 'B.BST', 'CST', 'AF CST', 'STM', 'WST', 'HPS', 'HPS (TRS)',
            'CRS', 'SLD (JKT)', 'BTB', 'SLD-BST', 'SLD-F.WST', 'SLD-B.WST', 'SLV (JKT)',
            'J/L', 'W/C LTH', 'MSL', 'WRT', 'T/L', 'INS (B)', 'INS (S)', 'CRT', 'THG', 'KNE', 'BTM', 'COL'
        ];
    
        foreach ($item['measurements'] as $mKey => $mDetails) {
            $shortCode = trim($mDetails['short_code'] ?? '');
            $title     = trim($mDetails['title'] ?? '');
            $uniqueKey = preg_replace('/[^A-Za-z0-9]/', '_', trim($shortCode . '_' . $title));
    
            if (!isset($this->unifiedMeasurements[$uniqueKey])) {
                $this->unifiedMeasurements[$uniqueKey] = [
                    'title'        => $title,
                    'short_code'   => $shortCode,
                    'value'        => $mDetails['value'] ?? '',
                    'remarks'      => $mDetails['remarks'] ?? '',
                    'show_remarks' => !empty($mDetails['show_remarks']),
                    'mappings'     => [[
                        'item_index'      => $itemIndex,
                        'measurement_key' => $mKey,
                    ]],
                ];
            } else {
                $alreadyMapped = collect($this->unifiedMeasurements[$uniqueKey]['mappings'])
                    ->where('item_index', $itemIndex)
                    ->where('measurement_key', $mKey)
                    ->isNotEmpty();
    
                if (!$alreadyMapped) {
                    $this->unifiedMeasurements[$uniqueKey]['mappings'][] = [
                        'item_index'      => $itemIndex,
                        'measurement_key' => $mKey,
                    ];
                }
            }
        }
    
        $sorted = [];
        foreach ($masterOrderOrder as $code) {
            foreach ($this->unifiedMeasurements as $uKey => $data) {
                if ($data['short_code'] === $code && !isset($sorted[$uKey])) {
                    $sorted[$uKey] = $data;
                }
            }
        }
        foreach ($this->unifiedMeasurements as $uKey => $data) {
            if (!isset($sorted[$uKey])) {
                $sorted[$uKey] = $data;
            }
        }
        $this->unifiedMeasurements = $sorted;
    }


  

    public function CategoryWiseProduct($categoryId, $index)
    {
        // Reset products for the selected item
        $this->items[$index]['products'] = [];
        $this->items[$index]['product_id'] = null;

        if ($categoryId) {
            // Fetch products based on the selected category and collection
            $this->items[$index]['products'] = Product::where('category_id', $categoryId)
                ->where('collection_id', $this->items[$index]['selected_collection']) // Ensure the selected collection is considered
                ->get();
        }
    }

    public function FindProduct($term, $index)
    {
        $collection = $this->items[$index]['selected_collection'];
        $category = $this->items[$index]['selected_category'];

        if (empty($collection)) {
            session()->flash('errorProduct.' . $index, 'Please select a collection before searching for a product.');
            return;
        }

        if (empty($category)) {
            session()->flash('errorProduct.' . $index, 'ðŸš¨ Please select a category before searching for a product.');
            return;
        }

        // Clear previous products in the current index
        $this->items[$index]['products'] = [];

        if (!empty($term)) {
            // Search for products within the specified collection and matching the term
            $this->items[$index]['products'] = Product::where('collection_id', $collection)
                ->where('category_id', $category)
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term . '%')
                          ->orWhere('product_code', 'like', '%' . $term . '%');
                })
                ->get();
        }

    }




    public function copyMeasurements($index)
    {
        $currentProductId = $this->items[$index]['product_id'] ?? null;
    
        if (empty($this->items[$index]['copy_previous_measurements'])) {
            // Checkbox unchecked → reset to original/preloaded values
            $this->resetMeasurements($index);
            return;
        }
    
        //  Look backward for same product first (latest updated values)
        for ($i = $index - 1; $i >= 0; $i--) {
            if (($this->items[$i]['product_id'] ?? null) === $currentProductId) {
                $this->items[$index]['measurements'] = $this->deepCopy($this->items[$i]['measurements']);
                return;
            }
        }
    
        //  Look forward for same product
        for ($i = $index + 1; $i < count($this->items); $i++) {
            if (($this->items[$i]['product_id'] ?? null) === $currentProductId) {
                $this->items[$index]['measurements'] = $this->deepCopy($this->items[$i]['measurements']);
                return;
            }
        }
    
        //  No same product found → copy only matching measurements from nearest item
        $found = false;
    
        for ($i = $index - 1; $i >= 0; $i--) {
            if (!empty($this->items[$i]['measurements'])) {
                $this->fillMatchingMeasurements($index, $i);
                $found = true;
                break;
            }
        }
    
        if (!$found) {
            for ($i = $index + 1; $i < count($this->items); $i++) {
                if (!empty($this->items[$i]['measurements'])) {
                    $this->fillMatchingMeasurements($index, $i);
                    break;
                }
            }
        }
    }

/**
 * Fill only matching measurements (by title or short_code) from a source item.
 */
 
 protected function fillMatchingMeasurements($currentIndex, $sourceIndex)
{
    foreach ($this->items[$currentIndex]['measurements'] as $key => &$measurement) {

        $measurementTitle = strtolower(trim($measurement['title'] ?? ''));

        $measurementShortCode = strtolower(trim($measurement['short_code'] ?? ''));

        foreach ($this->items[$sourceIndex]['measurements'] as $prev) {

            $prevTitle = strtolower(trim($prev['title'] ?? ''));

            $prevShortCode = strtolower(trim($prev['short_code'] ?? ''));

            // Match by title OR short_code
            if (
                (!empty($measurementTitle) && $measurementTitle === $prevTitle)
                ||
                (!empty($measurementShortCode) && $measurementShortCode === $prevShortCode)
            ) {

                // Copy value
                $measurement['value'] = $prev['value'] ?? null;

                // Copy remarks
                $measurement['remarks'] = $prev['remarks'] ?? null;

                // Auto open remarks box
                $measurement['show_remarks'] = !empty($prev['remarks']);

                break;
            }
        }
    }
}


// Helper: deep copy array to prevent Livewire reference issues
protected function deepCopy($array)
{
    return json_decode(json_encode($array), true);
}

// Reset measurements to original/preloaded values
protected function resetMeasurements($index)
{
    $productId = $this->items[$index]['product_id'] ?? null;
    if (!$productId) return;

    $measurements = Measurement::where('product_id', $productId)
        ->where('status', 1)
        ->orderBy('position', 'ASC')
        ->get();

    $previousValues = OrderMeasurement::whereHas('orderItem', function ($q) use ($productId) {
            $q->where('product_id', $productId)
              ->whereHas('order', function ($qq) {
                  $qq->where('customer_id', $this->customer_id);
              });
        })
        ->pluck('measurement_value', 'measurement_name')
        ->toArray();

    $this->items[$index]['measurements'] = $measurements->map(function ($m) use ($previousValues) {
        return [
            'id' => $m->id,
            'title' => $m->title,
            'short_code' => $m->short_code,
            'value' => $previousValues[$m->title] ?? ''
        ];
    })->toArray();
}




    public function TabChange($value)
    {
        // Initialize or reset error classes and messages
        $this->errorClass = [];
        $this->errorMessage = [];
        if ($value== 1) {
            $this->activeTab = $value;
        }
        if ($value > 1) {
            // Validate name
            if (empty($this->name)) {
                $this->errorClass['name'] = 'border-danger';
                $this->errorMessage['name'] = 'Please enter customer name';
            } else {
                $this->errorClass['name'] = null;
                $this->errorMessage['name'] = null;
            }

            // Validate business type
            if (empty($this->selectedBusinessType)) {
                $this->errorClass['selectedBusinessType'] = 'border-danger';
                $this->errorMessage['selectedBusinessType'] = 'Please select a business type';
            } else {
                $this->errorClass['selectedBusinessType'] = null;
                $this->errorMessage['selectedBusinessType'] = null;
            }


         // Validate Phone Number
         if (empty($this->phone)) {
            $this->errorClass['phone'] = 'border-danger';
            $this->errorMessage['phone'] = 'Please enter customer phone number';
        
          } else {
            $this->errorClass['phone'] = null;
            $this->errorMessage['phone'] = null;
        }
        
         if ($this->email) {
                if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                    $this->errorMessage['email'] = 'Please enter a valid email address.';
                    $this->errorClass['email'] = 'is-invalid';
                }  else {
                    $this->errorClass['email'] = null;
                    $this->errorMessage['email'] = null;
                }
            }


        // Validate Alternative Phone Number 1
        if (!empty($this->alternative_phone_number_1)) {
            if (!preg_match('/^\d{'. $this->mobileLengthAlt1 .'}$/', $this->alternative_phone_number_1)) {
                $this->errorClass['alternative_phone_number_1'] = 'border-danger';
                $this->errorMessage['alternative_phone_number_1'] = 'Alternative number 1 must be exactly ' . $this->mobileLengthAlt1 . ' digits';
            } else {
                $this->errorClass['alternative_phone_number_1'] = null;
                $this->errorMessage['alternative_phone_number_1'] = null;
            }
        }

        // Validate Alternative Phone Number 2
        if (!empty($this->alternative_phone_number_2)) {
            if (!preg_match('/^\d{'. $this->mobileLengthAlt2 .'}$/', $this->alternative_phone_number_2)) {
                $this->errorClass['alternative_phone_number_2'] = 'border-danger';
                $this->errorMessage['alternative_phone_number_2'] = 'Alternative number 2 must be exactly ' . $this->mobileLengthAlt2 . ' digits';
            } else {
                $this->errorClass['alternative_phone_number_2'] = null;
                $this->errorMessage['alternative_phone_number_2'] = null;
            }
        }

            // Validate Billing Information
            if (empty($this->billing_address)) {
                $this->errorClass['billing_address'] = 'border-danger';
                $this->errorMessage['billing_address'] = 'Please enter billing address';
            } else {
                $this->errorClass['billing_address'] = null;
                $this->errorMessage['billing_address'] = null;
            }

            if (empty($this->billing_city)) {
                $this->errorClass['billing_city'] = 'border-danger';
                $this->errorMessage['billing_city'] = 'Please enter billing city';
            } else {
                $this->errorClass['billing_city'] = null;
                $this->errorMessage['billing_city'] = null;
            }

            if (empty($this->billing_country)) {
                $this->errorClass['billing_country'] = 'border-danger';
                $this->errorMessage['billing_country'] = 'Please enter billing country';
            } else {
                $this->errorClass['billing_country'] = null;
                $this->errorMessage['billing_country'] = null;
            }

            // Check if both errorClass and errorMessage arrays are empty

            $errorClassNull = empty(array_filter($this->errorClass, function($val) {
                return !is_null($val);
            }));
            // If all values are null, set activeTab to the value passed
            if ($errorClassNull) {
                $this->activeTab = $value;
            }
             // Return the error classes and messages
            return [$this->errorClass, $this->errorMessage];
        }

    }



    public function checkproductPrice($value, $index)
    {
        $selectedFabricId = $this->items[$index]['selected_fabric'] ?? null;
        if ($selectedFabricId) {
            $fabricData = Fabric::find($selectedFabricId);
            if ($fabricData && floatval($value) < floatval($fabricData->threshold_price)) {
                // Error message for threshold price violation
                session()->flash('errorPrice.' . $index,
                    "The price for fabric '{$fabricData->title}' cannot be less than its threshold price of {$fabricData->threshold_price}.");
                return;
            }
        }

        // Sanitize and validate input value
        $formattedValue = preg_replace('/[^0-9.]/', '', $value);
        if (is_numeric($formattedValue)) {
            // If valid, format to two decimal places and update
            $this->items[$index]['price'] =$formattedValue;
            session()->forget('errorPrice.' . $index);
        } else {
            // Reset price and show error for invalid input
            // $this->items[$index]['price'] = 0;
            session()->flash('errorPrice.' . $index, 'Please enter a valid price.');
        }

        $this->updateBillingAmount(); // Update billing after validation
    }



    

    public function updatedNewUploads($value, $index)
    {
        if (!isset($this->imageUploads[$index])) {
            $this->imageUploads[$index] = [];
        }

        // Merge new uploads
        $this->imageUploads[$index] = array_merge($this->imageUploads[$index], $value);

        // Clear temporary uploads so input can be used again
        $this->newUploads[$index] = [];
    }


    public function removeImage($index, $imageIndex)
    {

        $orderItemId = $this->items[$index]['order_item_id'];


        $imagePath = OrderItemCatalogueImage::where('order_item_id', $orderItemId)
            ->skip($imageIndex)
            ->value('image_path');

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);


            OrderItemCatalogueImage::where('order_item_id', $orderItemId)
                ->where('image_path', $imagePath)
                ->delete();

            unset($this->existingImages[$index][$imageIndex]);
            $this->existingImages[$index] = array_values($this->existingImages[$index]);
        }
    }


    public function removeUploadedImage($index, $imageIndex){
        // Remove the image from the uploaded images array
        unset($this->imageUploads[$index][$imageIndex]);
        $this->imageUploads[$index] = array_values($this->imageUploads[$index]);
    }

    public function removeVideo($index, $audioIndex){
        ChangeTracker::setOrderId($this->orders->id);
        $orderItemId = $this->items[$index]['order_item_id'];
        $audioPath = OrderItemVoiceMessage::where('order_item_id', $orderItemId)
        ->skip($audioIndex)
        ->value('voices_path');

        if ($audioPath) {
            Storage::disk('public')->delete($audioPath);


            OrderItemVoiceMessage::where('order_item_id', $orderItemId)
            ->where('voices_path', $audioPath)
            ->get()
            ->each(function ($message) {
                $message->delete(); // This triggers the `deleted` event
            });

            unset($this->existingVideos[$index][$audioIndex]);
            $this->existingVideos[$index] = array_values($this->existingVideos[$index]);
        }
    }

    public function removeUploadedVoice($index, $voiceIndex){
        // Remove the audio from the uploaded audio array
        unset($this->voiceUploads[$index][$voiceIndex]);
        $this->voiceUploads[$index] = array_values($this->voiceUploads[$index]);
    }

    


    public function update(OrderRepository $orderRepo)
    {
        $this->validate();
        // dd($this->all());
        // Flatten unified changes back into structural elements before validating payloads
              if ($this->isUnifiedViewActive && !empty($this->unifiedMeasurements)) {
            
            // FORCE cast the entire property tree cleanly back to deep primitive plain PHP arrays
            $localItemsPayload = json_decode(json_encode($this->items), true);
        
            foreach ($this->unifiedMeasurements as $uniqueKey => $uField) {
                foreach ($uField['mappings'] as $mapping) {
                    $iIdx = $mapping['item_index'];
                    $mKey = $mapping['measurement_key'];
                    
                    if (isset($localItemsPayload[$iIdx]['measurements'][$mKey])) {
                        $localItemsPayload[$iIdx]['measurements'][$mKey]['value']   = $uField['value'];
                        $localItemsPayload[$iIdx]['measurements'][$mKey]['remarks'] = $uField['remarks'];
                    }
                }
            }
        
            // Re-assign plain pure arrays back smoothly
            $this->items = $localItemsPayload;
        }
        
        DB::beginTransaction();
        try {

            $total_product_amount = array_sum(array_column($this->items, 'price'));
            $total_amount = collect($this->items)->reduce(function ($carry, $item) {
                return $carry + ((float) $item['price'] * (int) $item['quantity']);
            }, 0);
            $airMail = floatval($this->air_mail);
            $total_amount += $airMail;
            ChangeTracker::setOrderId($this->orders->id);

            // Retrieve user details
            $user = User::find($this->customer_id);
            if (!$user) {
                // Create new user if not found
                $user = User::create([
                    'prefix' => $this->prefix,
                    'name' => $this->name,
                    'company_name' => $this->company_name,
                    'employee_rank' => $this->employee_rank,
                    'email' => $this->email,
                    'dob' => !empty($this->dob) ? $this->dob : null,
                    'phone' => $this->phone,
                    'user_type' => 1, // Customer
                    'alternative_phone_number_1' => $this->alternative_phone_number_1,
                    'alternative_phone_number_2' => $this->alternative_phone_number_2,
                    'business_type' => $this->selectedBusinessType,
                    'country_id' => $this->selectedCountryId,
                    'country_code' => $this->country_code,
                ]);
            } else {
                // Update existing user
                $user->update([
                    'prefix' => $this->prefix,
                    'name' => $this->name,
                    'company_name' => $this->company_name,
                    'employee_rank' => $this->employee_rank,
                    'email' => $this->email,
                    'dob' => !empty($this->dob) ? $this->dob : null,
                    'business_type' => $this->selectedBusinessType,
                    'country_id' => $this->selectedCountryId,
                    'country_code' => $this->country_code,


                    // 'country_id' => $this->country_id,
                    'country_code_phone' => $this->phone_code,
                    'phone' => $this->phone,
                    'country_code_alt_1'  => $this->alt_phone_code_1,
                    'alternative_phone_number_1' => $this->alternative_phone_number_1,
                    'country_code_alt_2'  => $this->alt_phone_code_2,
                    'alternative_phone_number_2' => $this->alternative_phone_number_2,
                ]);
            }
            // Update or create addresses
            $billingAddress = $user->address()->updateOrCreate(
                ['address_type' => 1], // Billing address
                [
                    'state' => $this->billing_state,
                    'city' => $this->billing_city,
                    'address' => $this->billing_address,
                    'landmark' => $this->billing_landmark,
                    'country' => $this->billing_country,
                    'zip_code' => $this->billing_pin,
                ]
            );

            // Update order details
            $name = $this->name;
            $email = $this->email;
            $billingadd = $this->billing_address;

            $billingLandmark= $this->billing_landmark;
            $billingCity= $this->billing_city;
            $billingState= $this->billing_state;
            $billingCountry= $this->billing_country;
            $billingPin= $this->billing_pin;

            $order = Order::find($this->orders->id);
            //die(ChangeTracker::getOrderId().'llll');
            if (!$order) {
                session()->flash('error', 'Order not found.');
                return redirect()->route('admin.order.index');
            }else{
                $previousPaidAmount = $order->paid_amount;
                $order->customer_id = $user->id;
                $order->prefix = $this->prefix;
                $order->customer_name = $this->name;
                $order->customer_email = $this->email;
                if(!empty($this->customer_image))
                    {
                        foreach($this->customer_image as $image){
                    
                            if(!is_string($image)){
                    
                                $path = Helper::handleFileUpload($image,'client_image');
                    
                                OrderMultipleFile::create([
                                    'order_id'=>$order->id,
                                    'file_type'=>'customer_image',
                                    'file_path'=>$path
                                ]);
                    
                            }
                        }
                    }
                    
                   if (!empty($this->physical_order_bill_book_new)) {

                        // DELETE OLD FILES FIRST (OVERRIDE LOGIC)
                        $oldFiles = OrderMultipleFile::where('order_id', $order->id)
                            ->where('file_type', 'bill_book_copy')
                            ->get();
                    
                        foreach ($oldFiles as $old) {
                    
                            // optional: delete physical file from storage
                            if (\Storage::disk('public')->exists($old->file_path)) {
                                \Storage::disk('public')->delete($old->file_path);
                            }
                    
                            $old->delete();
                        }
                    
                        // INSERT NEW FILES
                        foreach ($this->physical_order_bill_book_new as $file) {
                    
                            if (!is_string($file)) {
                    
                                $path = Helper::handleFileUpload($file, 'bill_book_copy');
                    
                                OrderMultipleFile::create([
                                    'order_id'  => $order->id,
                                    'file_type' => 'bill_book_copy',
                                    'file_path' => $path
                                ]);
                            }
                        }
                    }

                $order->billing_address = $billingadd;
                $order->billing_landmark = $billingLandmark;
                $order->billing_city = $billingCity;
                $order->billing_state = $billingState;
                $order->billing_country = $billingCountry;
                $order->billing_pin = $billingPin;
                
                $order->total_product_amount = $total_product_amount;
                $order->air_mail = $airMail;
                $order->total_amount = $total_amount;
                $order->last_payment_date = now();
                // $order->created_by = auth()->guard('admin')->user()->id;
                $loggedInAdmin = auth()->guard('admin')->user();
                // if ($order->team_lead_id === null || $loggedInAdmin->designation != 4) {
                // $order->team_lead_id = $loggedInAdmin->parent_id ?? null;
                // }
                $order->save();
            }

            
            foreach ($this->items as $key=>$item) {
                // dd($item);
                if($item['selected_collection'] == 1 && is_null($item['page_item'])){
                     // Only check if catalogue is not "No Catalogue Images"
                    if ($item['selectedCatalogue'] !== 'No Catalogue Images') {
                        $page = Page::where('page_number', $item['page_number'])
                                    ->where('catalogue_id', $item['selectedCatalogue'])
                                    ->first();
                        if($page){
                            $exists_pages = CataloguePageItem::where('page_id', $page->id)->get();
                            if($exists_pages->isNotEmpty() && empty($item['page_item'])){
                                $this->addError("items.$key.page_item", "Please select a page item for this page.");
                                return false;
                            }
                        }
                    }
                }
                if (!empty($item['order_item_id'])) {
                    // Find the existing OrderItem by its ID
                    $orderItem = OrderItem::find($item['order_item_id']);
                } else {
                    // Create a new OrderItem for new entries
                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->product_id = $item['product_id'];
                }
                // dd($item);
                if ($orderItem) {
                    $orderItem->product_id = $item['product_id'];
                    $orderItem->order_id = $order->id;
                    $orderItem->product_name = $item['searchproduct'];
                    $orderItem->remarks = $item['remarks'] ?? null;
                    $orderItem->status = $item['item_status'] ?? null;
                    $orderItem->quantity = $item['quantity'] ?? null;
                    $itemPrice = floatval($item['price']);
                    $orderItem->total_price = $itemPrice * $orderItem->quantity;
                    $orderItem->piece_price = $item['price'];
                    $orderItem->collection = $item['selected_collection'];
                    $orderItem->category = $item['selected_category'];
                    $orderItem->fabrics = $item['selected_fabric'];
                    $orderItem->catalogue_id = !empty($item['selectedCatalogue'])
                                                 ? $item['selectedCatalogue']
                                                : null;
                    $orderItem->quantity = ($item['selected_collection'] == 1) ? 1 : $item['quantity'];
                    $orderItem->fittings  = ($item['selected_collection'] == 1) ? $item['fitting'] : null;
                    $orderItem->expected_delivery_date  = $item['expected_delivery_date'];
                    $orderItem->cat_page_number  = $item['page_number'] ?? null;
                    $orderItem->cat_page_item  = $item['page_item'] ?? null;
                    $loggedInAdmin = auth()->guard('admin')->user();
                   
                    if (in_array($loggedInAdmin->designation, [1, 4])) {
                        if (isset($item['priority']) && !empty($item['priority'])) {
                            $orderItem->priority_level = $item['priority'];
                        }
                    }
                    

                    // ------------------------------------------------------------------
                    $previousStatus = $orderItem->getOriginal('status');   // null if new row
                    $newStatus      = $item['item_status'] ?? null;
                    $statusChanged  = $previousStatus !== $newStatus;

                    $orderItem->status = $newStatus;
                    // dd($orderItem);

                    // New 8-10-2025
                    if ($statusChanged) {

                        if ($newStatus === 'Process') {

                            // ── Case 1: Brand-new item (previousStatus == null)
                            if (is_null($previousStatus)) {

                                if (in_array($loggedInAdmin->designation, [1, 12])  && $order->created_by == 1) {
                                    // Admin creates or updates → auto approve & assign production
                                    $orderItem->tl_status    = 'Approved';
                                    $orderItem->admin_status = 'Approved';
                                    $orderItem->assigned_team = 'production';
                                } else {
                                    // Non-admin create → no auto approve
                                    $orderItem->tl_status    = 'Pending';
                                    $orderItem->admin_status = 'Pending';
                                    $orderItem->assigned_team = null;
                                }

                            // ── Case 2: Existing item changed from Hold → Process
                            } else {

                                if (in_array($loggedInAdmin->designation, [1, 12])  && $order->created_by == 1) {
                                    // Admin re-activates item → approve and assign production
                                    $orderItem->tl_status    = 'Approved';
                                    $orderItem->admin_status = 'Approved';
                                    $orderItem->assigned_team = 'production';
                                } else if ($loggedInAdmin->designation == 4) { // TL
                                    // If TL is editing and changes Hold -> Process
                                    $orderItem->tl_status    = 'Approved';  // Auto approve
                                    $orderItem->admin_status = 'Pending';   // Admin still needs to check
                                    $orderItem->assigned_team = 'production';
                                } else {
                                    // Other non-admin staff
                                    $orderItem->tl_status    = 'Pending';
                                    $orderItem->admin_status = 'Pending';
                                    $orderItem->assigned_team = null;
                                }

                                // Let system know order still needs admin review
                                $order->status = 'Approval Pending from TL';
                            }

                        } else {
                            // ── Case 3: Leaving Process (e.g. Hold, Cancel, etc.)
                            $orderItem->tl_status    = 'Pending';
                            $orderItem->admin_status = 'Pending';
                            $orderItem->assigned_team = null;
                        }

                        if ($loggedInAdmin && $loggedInAdmin->designation == 2 && ($previousStatus === 'Hold' || $newStatus === 'Process')) {
                            if ($order->status === 'On Hold') {
                                $order->status = 'Approval Pending from TL';
                                $order->save();
                                
                            }
                        }
                    }
                    // dd($order);


                        if (in_array($loggedInAdmin->designation, [1, 12])) {

                            $totalItems = $order->items()->count(); // 2
                        
                            // ✅ Count all items with Process status
                            $allProcessItems = $order->items()->where('status', 'Process')->count(); // 2
                        
                            // ✅ Check if all assigned teams are production or NULL
                            $allProductionTeam = $order->items()
                                ->whereNotNull('assigned_team')
                                ->where('assigned_team', '!=', 'production')
                                ->count() == 0; // true
                            
                            if ($totalItems > 0 && $allProcessItems == $totalItems && $allProductionTeam) {
                                $order->status = 'Fully Approved By Admin';
                            } else {
                               
                                $order->status = 'Partial Approved By Admin';
                            }
                        
                            $order->save();
                        }

                       
                        
                      
                        
                        


                    // ------------------------------------------------------------------

                    // Extra Fields
                    if ($item['selected_collection'] == 1) {
                        $extra = $this->extra_measurement[$key] ?? [];

                     /* ================= MEN JACKET ================= */
                        if (in_array('mens_jacket_suit',$extra)) {
                            $orderItem->shoulder_type = $item['shoulder_type'] ?? null;
                            $orderItem->mens_hand_stitching = $item['mens_hand_stitching'] ?? null;
                            $orderItem->vents = $item['vents'] ?? null;
                        } 

                        /* ================= LADIES JACKET ================= */
                        if (in_array('ladies_jacket_suit',$extra)) {
                            $orderItem->shoulder_type = $item['shoulder_type'] ?? null;
                            $orderItem->ladies_hand_stitching = $item['ladies_hand_stitching'] ?? null;
                            $orderItem->vents_required = $item['vents_required'] ?? null;
                            if ($orderItem->vents_required) {
                                $orderItem->vents_count = $item['vents_count'] ?? null;
                            }
                        } 

                      /* ================= TROUSER ================= */
                        if (in_array('trouser',$extra)) {
                            $orderItem->fold_cuff_required   = $item['fold_cuff_required'] ?? null;
                            if ($orderItem->fold_cuff_required == "Customized") {
                                $orderItem->fold_cuff_size  = $item['fold_cuff_size'] ?? null;
                            }else{
                                $orderItem->fold_cuff_size  = null;
                            }
                            $orderItem->pleats_required      = $item['pleats_required'] ?? null;
                           
                            $orderItem->back_pocket_required = $item['back_pocket_required'] ?? null;
                            
                            $orderItem->adjustable_belt      = $item['adjustable_belt'] ?? null;
                            $orderItem->suspender_button     = $item['suspender_button'] ?? null;
                            $orderItem->trouser_position     = $item['trouser_position'] ?? null;
                        }

                         /* ================= SHIRT ================= */
                        if (in_array('shirt',$extra)) {
                            $orderItem->sleeves       = $item['sleeves'] ?? null;          // L/S or H/S
                            $orderItem->collar        = $item['collar'] ?? null;           // Normal or Other
                            $orderItem->collar_style  = $item['collar_style'] ?? null;     // If "Other"
                            $orderItem->pocket        = $item['pocket'] ?? null;           // With / Without
                            $orderItem->cuffs         = $item['cuffs'] ?? null;            // Regular / French / Other
                            $orderItem->cuff_style    = $item['cuff_style'] ?? null;       // If "Other"
                        }
                        // if (in_array('ladies_jacket_suit',$extra) || in_array('shirt',$extra) || in_array('mens_jacket_suit',$extra)) {
                        //     $orderItem->client_name_required = $item['client_name_required'] ?? null;
                        //     if ($orderItem->client_name_required=="Yes") {
                        //         $orderItem->client_name_place = $item['client_name_place'] ?? null;
                        //         $orderItem->client_name_options = $item['client_name_options'] ?? null;
                        //     }else{
                        //         $orderItem->client_name_place = null;
                        //         $orderItem->client_name_options = null;
                        //     }
                        // }
                        
                        if (
                            in_array('ladies_jacket_suit',$extra) ||
                            in_array('shirt',$extra) ||
                            in_array('mens_jacket_suit',$extra)
                        ) {
                        
                            $orderItem->client_name_required =
                                $item['client_name_required'] ?? null;
                        
                        
                            if ($orderItem->client_name_required == "Yes") {
                        
                                // common for all
                                $orderItem->client_name_place =
                                    $item['client_name_place'] ?? null;
                        
                        
                                // only shirt
                                if (in_array('shirt',$extra)) {
                        
                                    $orderItem->client_name_options =
                                        $item['client_name_options'] ?? null;
                        
                                } else {
                        
                                    $orderItem->client_name_options = null;
                                }
                        
                        
                            } else {
                        
                                $orderItem->client_name_place = null;
                                $orderItem->client_name_options = null;
                            }
                        }
                    }

                    // dd($orderItem);
                    $orderItem->save();


                    if ($orderItem) {
                        if (!empty($this->imageUploads[$key])) {
                            foreach ($this->imageUploads[$key] as $uploadedImage) {
                                $path = $uploadedImage->store('uploads/order_item_catalogue_images', 'public');
                                OrderItemCatalogueImage::create([
                                    'order_item_id' => $orderItem->id,
                                    'image_path' => $path
                                ]);
                            }
                            // Clear uploaded images after saving
                            $this->imageUploads[$key] = [];
                        }
                    }

                    if ($orderItem) {
                        if (!empty($this->voiceUploads[$key])) {
                            foreach ($this->voiceUploads[$key] as $uploadedVoice) {
                                $path = $uploadedVoice->store('uploads/order_item_voice_messages', 'public');
                                OrderItemVoiceMessage::create([
                                    'order_item_id' => $orderItem->id,
                                    'voices_path' => $path
                                ]);
                            }
                            // Clear uploaded audios after saving
                            $this->voiceUploads[$key] = [];
                        }
                    }

                   

                    foreach ($item['measurements'] as $measurement) {
                        if (!isset($measurement['value']) || $measurement['value'] === '' ) {
                            DB::rollBack();
                            session()->flash('measurements_error.' . $key, "🚨 Oops! All measurement data should be mandatory.");
                            return;
                        }
                        
                        $measurementValue = $measurement['value'] ?? null;
                        $measurementName = $measurement['title'] ?? null;
                        $measurementShortCode = $measurement['short_code'] ?? null;
                        
                        // NEW
                        $showRemarks = !empty($measurement['show_remarks']);
                    
                        $remarks = $showRemarks
                            ? trim($measurement['remarks'] ?? '')
                            : null;

                        // Manually check if the OrderMeasurement exists
                        $orderMeasurement = OrderMeasurement::where('order_item_id', $orderItem->id)
                                                            ->where('measurement_name', $measurementName)
                                                            ->first();
                    
                        if ($orderMeasurement) {
                            $orderMeasurement->update([
                                'measurement_value' => $measurementValue,
                                // NEW
                                'remarks' => $remarks,
                    
                                'show_remarks' => $showRemarks,
                            ]);
                        } else {

                            // If the OrderMeasurement doesn't exist, create a new one
                            if(!empty($measurementValue)){
                                $data= OrderMeasurement::create([
                                    'order_item_id' => $orderItem->id,
                                    'measurement_name' =>  $measurementName,
                                    'measurement_title_prefix' =>  $measurementShortCode,
                                    'measurement_value' =>  $measurementValue,
                                    // NEW
                                    'remarks' => $remarks,
                    
                                    'show_remarks' => $showRemarks,
                                ]);
                            }

                        }
                    }

                    $orderItem = OrderItem::where('order_id', $order->id)->where('product_id', $item['product_id'])->first();
                    if($orderItem){
                        $orderItem->update([
                            'selected_fabric' => $item['selected_fabric'], // Save selected fabric ID
                        ]);
                    }

                }
            }

                //   $items = collect($this->items)
                    $items = $order->items()->get();
                
                    // Count Process items with TL approved
                    $processApprovedCount = $items->where('status', 'Process')
                                                ->where('tl_status', 'Approved')
                                                ->count();

                    // Count total Process items
                    $totalProcessCount = $items->where('status', 'Process')->count();
                
                    // Count Hold items
                    $holdCount = $items->where('status', 'Hold')->count();
                    // dd($processApprovedCount,$totalProcessCount,$holdCount);
                    // Determine order status based on TL designation
                    $loggedInAdmin = auth()->guard('admin')->user();
                    if ($loggedInAdmin->designation == 4) { // TL
                        if ($holdCount > 0 && $processApprovedCount > 0) {
                            $order->status = 'Partial Approved By TL';
                        } elseif ($processApprovedCount == $totalProcessCount && $totalProcessCount > 0) {
                            $order->status = 'Fully Approved By TL';
                        } elseif ($holdCount == $totalProcessCount) {
                            $order->status = 'Approval Pending from TL';
                        }
                    }

                    

                    // Save the updated order status
                    $order->save();
                    // dd($order);
                    $customerId = $user->id ?? $order->customer_id;

            // Store or update WhatsApp details if the flags are set
            $existingNumbers = UserWhatsapp::where('user_id', $customerId)->pluck('whatsapp_number')->toArray();

            $updatedNumbers = [];

            if ($this->isWhatsappPhone) {
                UserWhatsapp::updateOrCreate(
                    ['user_id' => $customerId, 'whatsapp_number' => $this->phone], // Search criteria
                    ['country_code' => $this->phone_code, 'updated_at' => now()]
                );
                $updatedNumbers[] = $this->phone;
            }

            if ($this->isWhatsappAlt1) {
                UserWhatsapp::updateOrCreate(
                    ['user_id' => $customerId, 'whatsapp_number' => $this->alternative_phone_number_1], // Search criteria
                    ['country_code' => $this->alt_phone_code_1, 'updated_at' => now()]
                );
                $updatedNumbers[] = $this->alternative_phone_number_1;
            }

            if ($this->isWhatsappAlt2) {
                UserWhatsapp::updateOrCreate(
                    ['user_id' => $customerId, 'whatsapp_number' => $this->alternative_phone_number_2], // Search criteria
                    ['country_code' => $this->alt_phone_code_2, 'updated_at' => now()]
                );
                $updatedNumbers[] = $this->alternative_phone_number_2;
            }

            // Delete records that were not updated
            UserWhatsapp::where('user_id', $customerId)
                ->whereNotIn('whatsapp_number', $updatedNumbers)
                ->delete();
                
            // Maintain Log
           $loggedInUserId = auth()->guard('admin')->user()->id ?? null;
            // Auto Approve for Admin And Store Person
            $staff = User::find($loggedInUserId);
            if ($staff && in_array($staff->designation, [1, 12])) {
                $orderRepo->approveOrder($order->id, $staff->id);
            }


            DB::commit();
            session()->flash('success', 'Order has been updated successfully.');
            //ChangeTracker::clear(); // to prevent accidental leakage into other requests
            return redirect()->route('admin.order.index');
        } catch (\Exception $e) {
            $this->dispatch('error_message');
            dd($e->getMessage());
            DB::rollBack();
            \Log::error('Error updating order: ' . $e->getMessage());
            session()->flash('error', $e->getMessage());
            session()->flash('error', 'Something went wrong. The operation has been rolled back.');
        }
    }
    


        /**
         * Helper function to calculate total amount
         */
        private function calculateTotalAmount()
        {
            return array_sum(array_column($this->items, 'price'));
        }

        /**
         * Helper function to calculate remaining amount
         */
        private function calculateRemainingAmount()
        {
            return $this->calculateTotalAmount() - $this->paid_amount;
        }

        /**
         * Helper function to format address
         */
        private function formatAddress($address, $landmark, $city, $state, $country, $pin)
        {
            return "{$address}, {$landmark}, {$city}, {$state}, {$country} - {$pin}";
        }



    public function resetForm()
    {
        // Reset all the form properties
        $this->reset([
            'name',
            'company_name',
            'employee_rank',
            'email',
            'dob',
            'phone',

        ]);
    }
    public function updateMobileLengths()
    {
        $this->mobileLengthPhone = Country::where('country_code', $this->phone_code)->value('mobile_length') ?? '';
        $this->mobileLengthAlt1 = Country::where('country_code', $this->alt_phone_code_1)->value('mobile_length') ?? '';
        $this->mobileLengthAlt2 = Country::where('country_code', $this->alt_phone_code_2)->value('mobile_length') ?? '';
    }
    public function CountryCodeSet($selector, $Code, $number = null)
    {
        $mobile_length = Country::where('country_code', $Code)->value('mobile_length') ?? '';
        // Determine which field to update based on selector
        if (strpos($selector, 'mobile') !== false || strpos($selector, '#mobile') !== false) {
            $this->mobileLengthPhone = $mobile_length;
        } elseif (strpos($selector, 'alt_phone_1') !== false) {
            $this->mobileLengthAlt1 = $mobile_length;
        } elseif (strpos($selector, 'alt_phone_2') !== false) {
            $this->mobileLengthAlt2 = $mobile_length;
        }
    
        // Dispatch for maxlength
        $this->dispatch('update_input_max_length', [
            'id' => $selector,
            'mobile_length' => $mobile_length
        ]);
        

        
    }

    public function render()
    {
        // dd($this->order);
        return view('livewire.order.order-edit' ,[
            'order' => $this->orders
        ]);
    }
}
