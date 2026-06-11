<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Collection;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection as BaseCollection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;


class OrderImport implements ToCollection
{
//     public function collection(BaseCollection $rows)
// {
//     DB::beginTransaction();

//     try {
//         $rows->shift(); // Remove header row

//         // group all rows by order number (column 0)
//         $grouped = $rows->filter(fn($row) => !empty($row[0]))
//                         ->groupBy(fn($row) => preg_replace('/[^0-9]/', '', $row[0]));

//         $lastOrderNumber = Order::latest('order_number')->value('order_number') ?? 10000;

//         foreach ($grouped as $orderNumber => $items) {

//             // Handle duplicate order numbers
//             if (Order::where('order_number', $orderNumber)->exists()) {
//                 $lastOrderNumber++;
//                 $orderNumber = $lastOrderNumber;
//             } else {
//                 $lastOrderNumber = max($lastOrderNumber, $orderNumber);
//             }

//             // Use first row for order-level data (name, address, etc.)
//             $firstRow = $items->first();
//             $prefix   = $firstRow[1];
//             $name     = $firstRow[2];
//             $code     = $firstRow[3];
//             $phone    = $firstRow[4];
//             $address  = $firstRow[5];
//             $city     = $firstRow[6];
//             $country  = $firstRow[7];

//             // Sum total amount across all items in this order
//             $totalAmount = $items->sum(fn($row) => (float) $row[11]);

//             // User create or find
//             $user = User::firstOrCreate(
//                 ['phone' => $phone],
//                 [
//                     'prefix'             => $prefix,
//                     'name'               => $name,
//                     'country_code_phone' => $code,
//                     'user_type'          => 1,
//                     'created_by'         => auth()->guard('admin')->id(),
//                 ]
//             );

//             // Create Order ONCE for the group
//             $order = Order::create([
//                 'order_number'    => $orderNumber,
//                 'customer_id'     => $user->id,
//                 'prefix'          => $prefix,
//                 'customer_name'   => $name,
//                 'billing_address' => $address,
//                 'billing_city'    => $city,
//                 'billing_country' => $country,
//                 'total_amount'    => $totalAmount,
//                 'created_by'      => auth()->guard('admin')->id(),
//             ]);

//             // Loop each product row and create an OrderItem per row
//             foreach ($items as $row) {
//                 $collection = strtoupper($row[8]);
//                 $category   = strtoupper($row[9]);
//                 $product    = $row[10];
//                 $price      = (float) $row[11];

//                 $collectionIdMap = ['GARMENT' => 1, 'ACCESSORIES' => 2];
//                 if (!isset($collectionIdMap[$collection])) {
//                     throw new \Exception("Order {$orderNumber}: Invalid collection '{$collection}'");
//                 }
//                 $collectionId = $collectionIdMap[$collection];

//                 $categoryIdMap = [
//                     1 => ['SUITING' => 1, 'SHIRTING' => 6, 'TROUSER' => 7],
//                     2 => ['ACCESSORIES' => 3],
//                 ];
//                 if (!isset($categoryIdMap[$collectionId][$category])) {
//                     throw new \Exception("Order {$orderNumber}: Category '{$category}' not valid for '{$collection}'");
//                 }
//                 $categoryId = $categoryIdMap[$collectionId][$category];

//                 $productExists = Product::where('name', $product)
//                     ->where('collection_id', $collectionId)
//                     ->where('category_id', $categoryId)
//                     ->exists();

//                 if (!$productExists) {
//                     throw new \Exception("Order {$orderNumber}: Product '{$product}' not found");
//                 }

//                 OrderItem::create([
//                     'order_id'     => $order->id,
//                     'product_name' => $product,
//                     'collection'   => $collectionId,
//                     'category'     => $categoryId,
//                     'piece_price'  => $price,
//                     'quantity'     => 1,
//                     'total_price'  => $price,
//                     'status'       => 'Hold',
//                 ]);
//             }
//         }

//         DB::commit();

//     } catch (\Exception $e) {
//         DB::rollBack();
//         throw $e;
//     }
// }

   public function collection(BaseCollection $rows)
    {
        DB::beginTransaction();

        try {

            // Remove Header Row
            $rows->shift();

            foreach ($rows as $row) {

                // Skip empty row
                if (empty($row[0])) {
                    continue;
                }

                // Get order number
                $orderNumber = trim($row[0]);

                // Remove non numeric chars
                $orderNumber = preg_replace('/[^0-9]/', '', $orderNumber);

                if (empty($orderNumber)) {
                    continue;
                }

                // Check duplicate
                if (Order::where('order_number', $orderNumber)->exists()) {
                    throw new \Exception("Duplicate Order Number: {$orderNumber}");
                }

                // Create Order
                Order::create([
                    'order_number' => $orderNumber,
                    'status'       => 'On Hold',
                    'created_by'   => auth()->guard('admin')->id(),
                ]);

                // Find Billing Range
                $billingRange = DB::table('salesman_billing_number')
                    ->where('start_no', '<=', $orderNumber)
                    ->where('end_no', '>=', $orderNumber)
                    ->first();

                // Increase Used Count
                if ($billingRange) {

                    DB::table('salesman_billing_number')
                        ->where('id', $billingRange->id)
                        ->increment('no_of_used');
                }
            }

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}