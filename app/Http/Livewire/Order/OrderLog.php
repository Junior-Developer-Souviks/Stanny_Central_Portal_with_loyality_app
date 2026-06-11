<?php

namespace App\Http\Livewire\Order;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Helpers\Helper;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\ChangeLog;
use App\Models\OrderItem;
use App\Models\OrderMeasurement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
// use Barryvdh\DomPDF\Facade as PDF;
// use Barryvdh\DomPDF\PDF;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rules\Date;

class OrderLog extends Component
{
    use WithPagination;

    public $customer_id;
    public $created_by, $search,$status,$start_date,$end_date,$order,
    $ignore_fields=['id','product_id','customer_email'],
    $relational_tables=['collection','category'],
    $fields=['company_name'=>'Company Name','product_name'=>'Product','total_price'=>'Total Price',
    'total_amount'=>'Total Amount','alternative_phone_number_1'=>'Alternative No(1)',
      'alternative_phone_number_2'=>'Alternative No(2)','billing_address'=>'Billing Address',
      'priority_level'=>'Priority Level','expected_delivery_date'=>'Expected Delivery',
      'country_code_alt_1'=>'Country Code(1)', 'country_code_alt_2'=>'Country Code(2)','customer_image'=>'Customer Image'
];

    public $invoiceId;
    public $orderId;
    public $totalPrice;
    public $auth;

    public $tab = 'all';
    // protected $listeners = ['cancelOrder'];
    protected $listeners = ['cancelOrder','markReceivedConfirmed','deliveredToCustomer','deliveredToCustomerPartial'];

    protected $paginationTheme = 'bootstrap'; // Optional: For Bootstrap styling

    public function changeTab($status){
        $this->tab = $status;
        $this->resetPage();
    }
    public function resetForm(){
        $this->reset(['search', 'start_date','end_date','created_by','status']);
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function mount($id = null)
    {
        $this->orderId = $id; // Store the customer_id if provided
        $this->order = Order::select('id','order_number','created_at')->findOrFail($this->orderId);

    }
    public function FindCustomer($keywords){
        $this->search = $keywords;
    }
    public function AddStartDate($date){
        $this->start_date = $date;
    }
    public function AddEndDate($date){
        $this->end_date = $date;
    }
    public function CollectedBy($staff_id){
        $this->created_by = $staff_id;
    }
    public function setStatus($status){
        $this->status = $status;
    }
    public function render()
    {
        $placed_by = User::where('user_type', 0)->get();
        $auth = Auth::guard('admin')->user();
        $sl_no=1;
        $logs=ChangeLog::where('order_id',$this->orderId)
        ->with('user:id,name')
        ->where('purpose', '!=', 'delivery_proceed')
        ->orderBy('created_at', 'desc')  // Order by created_at in descending order
        ->get();

        $logs = $logs->map(function ($item) use (&$sl_no)  {
            $data_details=json_decode($item->data_details,true) ?? [];
            $before = $data_details['before'] ?? null; // stdClass
            $after = $data_details['after'] ?? null;

            // Convert to array recursively
            
            $item->before=trim($this->renderDiff($before ?? []));

            $item->after=$this->renderDiff($after ?? []);


            $item->sl_no=$sl_no++;

            return $item;
        });
        return view('livewire.order.order-log', [
            'logs' => $logs,
            'order'=>$this->order
        ]);
    }
    // private function renderDiff($data)
    // {
    //     $label="";
    //     foreach($data as $key =>$val)
    //     {

    //       if($key=='items')
    //       {

    //         if($this->isAssoc($val))
    //         {
    //             $label.='Item>>'.$this->fetchItem($val['id']);
    //             foreach($val as $key=> $sub_val)
    //             {
    //                 //$label.=$sub_val;
    //              if(is_array($sub_val))
    //              {
    //                 $label.='>>'.$this->subObject($sub_val,$key);

    //                 //$label.=$this->subObject($sub_val,$key);exit;
    //              }
    //              else{
    //                 if(!in_array($key,$this->ignore_fields))
    //                 {
    //                     if($key=='expected_delivery_date')
    //                     {
    //                         $sub_val= Date('Y-m-d',strtotime($sub_val));
    //                     }
    //                     $title=Str::title(value: $key);
    //                     if(!empty($this->fields[$key]))
    //                     {
    //                         $title=$this->fields[$key];
    //                     }
    //                     if(empty($sub_val))
    //                     {
    //                         $sub_val="N/A";
    //                     }
    //                     $label.='>>'.$title.'>>'.$sub_val;

    //                 }
    //              }
    //             }
    //         }
    //         else{
    //             foreach($val as $key=> $sub_val)
    //             {
    //             $label.='Item>>'.$this->fetchItem($sub_val['id']);
    //             foreach($sub_val as $sub_key=> $sub_sub_val)
    //             {
    //                 //$label.=$sub_val;
    //                 if(is_array($sub_sub_val))
    //                 {
    //                 $label.='>>'.$this->subObject($sub_sub_val,$sub_key);

    //                 //$label.=$this->subObject($sub_val,$key);exit;
    //                 }
    //                 else{
    //                 if(!in_array($sub_key,$this->ignore_fields))
    //                 {
    //                     $title=Str::title(value: $sub_key);
    //                     if(!empty($this->fields[$sub_key]))
    //                     {
    //                         $title=$this->fields[$sub_key];
    //                     }
    //                     if($sub_key=='expected_delivery_date')
    //                     {
    //                         $sub_sub_val= Date('Y-m-d',strtotime($sub_sub_val));
    //                         $label.='>>'.$title.'>>'.$sub_sub_val;


    //                     }
    //                     else if(in_array($sub_key,$this->relational_tables))
    //                     {
    //                         if(empty($sub_sub_val))
    //                         {
    //                             $sub_sub_val="N/A";
    //                         }
    //                         $label.='>>'.$this->subObject(['id'=>$sub_sub_val],$sub_key);

    //                     }

    //                     else{
    //                         if(empty($sub_sub_val))
    //                         {
    //                             $sub_sub_val="N/A";
    //                         }
    //                         $label.='>>'.$title.'>>'.$sub_sub_val;

    //                     }

    //                 }
    //                 }
    //                 $label.='<br>';
    //             }

    //             }

    //         }
    //       }
    //       else{
    //          if($key=='customer')
    //          {
    //             $label.='Customer>>';
    //          }
    //         foreach($val as $key=> $sub_val)
    //         {

    //             foreach($sub_val as $sub_key=> $sub_sub_val)
    //             {
    //                  $title=Str::title($sub_key);

    //                  if(!empty($this->fields[$sub_key]))
    //                  {

    //                     $title=$this->fields[$sub_key];
    //                  }
    //                 if(!in_array($sub_key,$this->ignore_fields))
    //                 {
    //                     if ($sub_key=='dob')
    //                     {
    //                         $sub_sub_val= Date('Y-m-d',strtotime($sub_sub_val));
    //                     }
    //                     else if($sub_key=='customer_image'){
    //                         if(!empty($sub_sub_val))
    //                         {
    //                             $sub_sub_val='<img src="'.asset($sub_sub_val).'" alt="" class="img-thumbnail" width="100">';

    //                         }
    //                     }
    //                     if(empty($sub_sub_val))
    //                     {
    //                         $sub_sub_val="N/A";
    //                     }
    //                     $label.=$title.'>>'.$sub_sub_val;
    //                 }
    //                 $label.="<br>";
    //             }

    //         }
    //       }

    //     }
    //     //echo $label;
    //     return  preg_replace('/\s*>>\s*/', '>>', $label);
    // }

    private function renderDiff($data)
{
    $label = "";

    foreach ($data as $key => $val) {
        if ($key == 'items') {

            if ($this->isAssoc($val)) {
                $label .= 'Item>>' . $this->fetchItem($val['id']);

                foreach ($val as $subKey => $subVal) {
                    if (is_array($subVal)) {
                        $label .= '>>' . $this->subObject($subVal, $subKey);
                    } else {
                        if (!in_array($subKey, $this->ignore_fields)) {
                            if ($subKey == 'expected_delivery_date') {
                                $subVal = Date('Y-m-d', strtotime($subVal));
                            }
                            $title = $this->fields[$subKey] ?? Str::title($subKey);
                            $subVal = $subVal ?: "N/A";
                            $label .= '>>' . $title . '>>' . $subVal;
                        }
                    }
                }
            } else {
                if (is_iterable($val)) {
                    foreach ($val as $subVal) {
                        if (!is_array($subVal)) {
                            continue; // skip non-arrays safely
                        }
                        $label .= 'Item>>' . $this->fetchItem($subVal['id']);

                        foreach ($subVal as $subKey => $subSubVal) {
                            if (is_array($subSubVal)) {
                                $label .= '>>' . $this->subObject($subSubVal, $subKey);
                            } else {
                                if (!in_array($subKey, $this->ignore_fields)) {
                                    $title = $this->fields[$subKey] ?? Str::title($subKey);

                                    if ($subKey == 'expected_delivery_date') {
                                        $subSubVal = Date('Y-m-d', strtotime($subSubVal));
                                    } elseif (in_array($subKey, $this->relational_tables)) {
                                        $subSubVal = $subSubVal ?: "N/A";
                                        $label .= '>>' . $this->subObject(['id' => $subSubVal], $subKey);
                                        continue;
                                    }

                                    $subSubVal = $subSubVal ?: "N/A";
                                    $label .= '>>' . $title . '>>' . $subSubVal;
                                }
                            }
                            $label .= '<br>';
                        }
                    }
                }
            }
        } else {
            if ($key == 'customer') {
                $label .= 'Customer>>';
            }

            if (is_iterable($val)) {
                foreach ($val as $subVal) {
                    if (!is_iterable($subVal)) {
                        continue; // skip if it’s just int/string
                    }

                    foreach ($subVal as $subKey => $subSubVal) {
                        $title = $this->fields[$subKey] ?? Str::title($subKey);

                        if (!in_array($subKey, $this->ignore_fields)) {
                            if ($subKey == 'dob') {
                                $subSubVal = Date('Y-m-d', strtotime($subSubVal));
                            } elseif ($subKey == 'customer_image' && !empty($subSubVal)) {
                                $subSubVal = '<img src="' . asset($subSubVal) . '" alt="" class="img-thumbnail" width="100">';
                            }
                            $subSubVal = $subSubVal ?: "N/A";
                            $label .= $title . '>>' . $subSubVal;
                        }
                        $label .= "<br>";
                    }
                }
            }
        }
    }

    return preg_replace('/\s*>>\s*/', '>>', $label);
}

    private function isIndexed(array $arr): bool
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * Returns true if $arr is an “associative” (object‑like) array:
     *   ['foo' => ..., 'bar' => ...]
     */
    private function isAssoc(array $arr): bool
    {
        return ! $this->isIndexed($arr);
    }
    private function fetchItem($id)
    {
        $item=OrderItem::findOrFail($id);
        return  $item->product_name;
    }

    private function subObject($arr,$sub_label)
    {
        $label = "";
        if($sub_label=='measurements')
        {

            $label="Measurements>>";
            $item=OrderMeasurement::findOrFail($arr['id']);
            $label.=$item->measurement_name.'>>'.$arr['measurement_value'];
        }
        if($sub_label=='collection')
        {

            $label="Collection>>";
            $item=\App\Models\Collection::findOrFail($arr['id']);
            $label.=$item->title;

        }
        if($sub_label=='category')
        {

            $label="Category>>";
            $item=\App\Models\Category::findOrFail($arr['id']);
            $label.=$item->title;

        }
        return preg_replace('/\s*>>\s*/', '>>', $label);
    }




}
