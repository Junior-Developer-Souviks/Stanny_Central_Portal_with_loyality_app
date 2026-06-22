<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\SalesmanBilling;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\StockFabric;
use App\Models\StockProduct;
use App\Models\OrderStockEntry;

class Helper
{
    /**
     * Handle image upload and store in a new folder.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @param string $folderName
     * @return string The file path of the uploaded image
     */
     
      public static function encryptData($value)
        {
            return Crypt::encryptString($value);
        }
        
       public static function decryptData($value)
        {
            return Crypt::decryptString($value);
        }

    public static function uploadImage($image, $folderName)
    {
        // Ensure the image is valid
        if (!$image->isValid()) {
            throw new \Exception("Invalid image file.");
        }

        $folderPath = 'uploads/' . DIRECTORY_SEPARATOR . $folderName;

        // Generate a unique filename
        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
        // Use Laravel's Storage facade for managing file uploads
       

        try {
            // Save the file to the desired location
            $image->storeAs($folderPath, $filename, 'public');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            throw new \Exception("Image could not be moved. Error: " . $e->getMessage());
        }
        // Return the relative path of the uploaded file
        return 'uploads/' . $folderName . '/' . $filename;
    }

    public static function handleFileUpload($file, $folder)
    {
        if ($file && $file instanceof \Illuminate\Http\UploadedFile) {
            $timestamp = now()->timestamp;
            $fileName = $timestamp . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs($folder, $fileName, 'public');
            return "storage/" . $storedPath;
        }
        return null;
    }

    public static function generateInvoiceBill($salesManId)
    {
        // Fetch Salesman Details
        $salesman = User::find($salesManId);

        if (!$salesman) {
            return [
                'number' => 'XXX-000',
                'status' => 1,
                'bill_id' => null,
            ];
        }

        // Extract the first 3 characters of the salesman's name and convert to uppercase
        // $prefix = strtoupper(substr($salesman->name, 0, 3));

        // Check for salesman billing record
        $salesmanBillBook = SalesmanBilling::where('salesman_id', $salesManId)
            ->whereColumn('total_count', '>', 'no_of_used')
            ->first();

        if ($salesmanBillBook) {
            $new_number = $salesmanBillBook->start_no + $salesmanBillBook->no_of_used;

            do {
                // Format the order number with prefix and zero-padding
                $formatted_number =  str_pad($new_number, 3, '0', STR_PAD_LEFT);

                // Check if the order number already exists
                $existing_order = Order::where('order_number', $formatted_number)->first();
                
                // If order number exists, increment it
                if ($existing_order) {
                    $new_number++;
                }

                // Continue loop while the new_number is within the allowed range
            } while ($existing_order && $new_number <= $salesmanBillBook->end_no);

            // If we exit the loop and new_number is still valid, proceed
            if ($new_number <= $salesmanBillBook->end_no) {
                return [
                    'number' => $formatted_number,
                    'status' => 1,
                    'bill_id' => $salesmanBillBook->id,
                ];
            }
        }

        return [
            'number' => '000',
            'status' => 1,
            'bill_id' => null,
        ];
    }


    public static function generateUniqueNumber($increment = 0) {
        return now()->format('YmdHis') . str_pad($increment, 3, '0', STR_PAD_LEFT);
    }
    

    public static function generateTransactionId(){
        return 'PAYMENT'.now()->format('YmdHis');
    }

    public static function GetCustomerDetails($term)
    {
        $auth = Auth::guard('admin')->user();
        if (!empty($term)) {
            return User::where('user_type', 1)
            ->where('status', 1)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('phone', 'like', '%' . $term . '%')
                    ->orWhere('whatsapp_no', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%')
                    ->orWhereHas('orders', function ($q) use ($term) { // Include orders filter
                        $q->where('order_number', 'like', '%' . $term . '%')
                            ->orWhere('customer_name', 'like', '%' . $term . '%')
                            ->orWhere('customer_email', 'like', '%' . $term . '%');
                    });
            })
            // ->when(!$auth->is_super_admin, fn($query) => $query->where('created_by', $auth->id)) // Restrict non-admins
            ->withCount('orders') // Optionally, count the orders for reference
            ->take(20)
            ->get();

        } else {
            // Reset results when the search term is empty
            return [];
        }
    }
    
    public static function getCrDr($amount){
        if($amount > 0){
            return "Cr"; # if postive +
        } else if($amount < 0) {
            return "Dr"; # if negative -
        } else {
            return "";
        }
    }
    
    public static function replaceMinusSign($number){
        return str_replace("-","",$number);
    }

    public static function getNamePrefixes(){
        return ['Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Adv.', 'Me.'];
    }

  

    public static function getStockEntryData($collectionId, $fabricId = null, $productId = null, $orderId = null, $orderItemId = null)
    {
        if ($collectionId == 1) {
            $fabricStock = StockFabric::where('fabric_id', $fabricId)->first();
            $globalAvailable = $fabricStock ? (int)$fabricStock->qty_in_meter : 0;

            // Compute how much has been reserved for this fabric in this order
            $reserved = OrderStockEntry::where('order_id', $orderId)
                ->where('fabric_id', $fabricId)
                ->when($orderItemId, fn($q) => $q->where('order_item_id', '!=', $orderItemId)) 
                ->sum('quantity');

            return [
                'available_label' => 'Available Meter',
                'updated_label'   => 'Required Meter',
                'available_value' => max($globalAvailable - $reserved, 0),
                'input_name'      => 'updated_meter',
                'type'            => 'meter'
            ];
        } elseif ($collectionId == 2) {
            $productStock = StockProduct::where('product_id', $productId)->first();
            $globalAvailable = $productStock ? (int)$productStock->qty_in_pieces : 0;

            $reserved = OrderStockEntry::where('order_id', $orderId)
                ->where('product_id', $productId)
                ->when($orderItemId, fn($q) => $q->where('order_item_id', '!=', $orderItemId))
                ->sum('quantity');

            return [
                'available_label' => 'Available Pcs',
                'updated_label'   => 'Required Pcs',
                'available_value' => max($globalAvailable - $reserved, 0),
                'input_name'      => 'updated_pcs',
                'type'            => 'pcs'
            ];
        }

        return null;
    }
    
     public static function ExtraRequiredMeasurement($product_name)
    {
        $map = [
            "MEN'S JACKET"     => ["mens_jacket_suit"],
            "MEN'S 2 PC SUIT (JKT+TRS)"     => ["mens_jacket_suit","trouser"],
            "MEN'S 3 PC SUIT (JKT+TRS+W/C)"     => ["mens_jacket_suit","trouser"],
            "LADIES JACKET"    => ["ladies_jacket_suit"],
            "LADIES SUIT (L.JKT+L.TRS)"    => ["ladies_jacket_suit","trouser"],
            "LADIES SUIT (L.JKT+L.SKT)"    => ["ladies_jacket_suit"],
            "LADIES SUIT (L.JKT+L.SKT+L.W/C)" => ["ladies_jacket_suit"],
            "LADIES 3 PC SUIT (L.JKT+L.TRS+L.W/C)"    => ["ladies_jacket_suit","trouser"],
            "MEN'S TROUSER"  => ["trouser"],
            "LADIES TROUSER" => ["trouser"],
            "MEN'S KURTA" => ["trouser"],
            "MEN'S KURTA SET" => ["trouser"],
            "LADIES SAFARI SET (L. SAF SH+L.TRS)" => ["trouser"],
            "MEN'S SHIRT" => ["shirt"],
            "MEN'S HUNTING SHIRT" => ["shirt"],
            "MEN'S SAFARI SHIRT" => ["shirt"],
            "LADIES SHIRT" => ["shirt"],
            "LADIES SAFARI SHIRT" => ["shirt"],
            "LADIES HUNTING SHIRT" => ["shirt"],
        ];

        $name = strtoupper(trim($product_name));

        return $map[$name] ?? [];
    }
    
 
   


}
