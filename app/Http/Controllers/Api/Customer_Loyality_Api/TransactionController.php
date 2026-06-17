<?php

namespace App\Http\Controllers\Api\Customer_Loyality_Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function rewardsHistory(Request $request)
    {
        $user = $request->user();

        $type = $request->type; // active | used | expired


        /*
        |--------------------------------------------------------------------------
        | ACTIVE
        |--------------------------------------------------------------------------
        */

       if ($type == 'active') {

            $user = User::find($user->id);


            // POINT EXPIRY
            $pointExpiry = WalletTransaction::where('user_id', $user->id)
                ->where('type','credit')
                ->where('points','>',0)
                ->where('balance_before','<=',$user->total_points)
                ->where('balance_after','>=',$user->total_points)
                ->where('expiry_date','>=',now())
                ->orderBy('id','desc')
                ->first();



            // LOUNGE EXPIRY
            $loungeExpiry = WalletTransaction::where('user_id', $user->id)
                ->where('type','credit')
                ->where('lounge_visits','>',0)
                ->where('lounge_before','<=',
                    ($user->lounge_visits_total ?? 0)
                    -
                    ($user->lounge_visits_used ?? 0)
                )
                ->where('lounge_after','>=',
                    ($user->lounge_visits_total ?? 0)
                    -
                    ($user->lounge_visits_used ?? 0)
                )
                ->where('expiry_date','>=',now())
                ->orderBy('id','desc')
                ->first();



            return response()->json([
                'status'=>true,
                'type'=>'active',
                'data'=>[

                    'active_points'=>$user->total_points ?? 0,


                    'active_lounge'=>
                        ($user->lounge_visits_total ?? 0)
                        -
                        ($user->lounge_visits_used ?? 0),


                    'expiry_date_points'=>$pointExpiry
                        ? $pointExpiry->expiry_date
                        : null,


                    'expiry_date_lounge'=>$loungeExpiry
                        ? $loungeExpiry->expiry_date
                        : null,

                ]
            ]);
        }


        $query = WalletTransaction::where(
            'user_id',
            $user->id
        );


        /*
        |--------------------------------------------------------------------------
        | USED
        |--------------------------------------------------------------------------
        */

        if ($type == 'used') {


            $query->where('type','debit')
                ->selectRaw('
                    MAX(id) as id,
                    source,
                    channel,

                    SUM(points) as used_points,

                    SUM(lounge_visits) as used_lounge,

                    MAX(expiry_date) as expiry_date,

                    MAX(is_expired) as is_expired,

                    MAX(created_at) as created_at,

                    DATE(created_at) as date
                ')
                ->groupBy(
                    'source',
                    'channel',
                    DB::raw('DATE(created_at)')
                );
        }



        /*
        |--------------------------------------------------------------------------
        | EXPIRED
        |--------------------------------------------------------------------------
        */

        if ($type == 'expired') {


            $query->where(function($q){

                $q->where('is_expired',1)
                ->orWhere('expiry_date','<',now());

            });

        }



        $data = $query
            ->orderBy('id','desc')
            ->get();



        return response()->json([
            'status'=>true,
            'type'=>$type,
            'data'=>$this->formatRewards($data,$type)
        ]);

    }





    private function formatRewards($transactions,$type)
    {

        return $transactions->map(function($item) use ($type){


            return [

                'id'=>$item->id,


                'title'=>ucfirst(
                    $item->channel ?? 'Reward'
                ),


                'sub_title'=>$item->source,


                /*
                USED TAB:
                show summed value
                */

                'points'=>
                    $item->used_points
                    ??
                    $item->points
                    ??
                    0,


                'lounge_visits'=>
                    $item->used_lounge
                    ??
                    $item->lounge_visits
                    ??
                    0,


                'type'=>$item->type ?? $type,


                'expiry_date'=>$item->expiry_date,


                'created_at'=>


                    $item->created_at

                    ?

                    date(
                        'd M Y',
                        strtotime($item->created_at)
                    )

                    :

                    ($item->date ?? null),

            ];

        });

    }

   public function customerTransactionHistory(Request $request)
{
    $user = $request->user();

    $customer = User::find($user->id);

    if (!$customer) {
        return response()->json([
            'status'=>false,
            'message'=>'Customer not found'
        ],404);
    }


    /*
    |--------------------------------------------------------------------------
    | Past Purchases
    |--------------------------------------------------------------------------
    */

    $purchases = Order::where('customer_id',$user->id)
        ->orderBy('id','desc')
        ->get()
        ->map(function($order){
             $paymentStatus = null;
             if($order->invoice){
                if($order->invoice->payment_status == 2){
                    $paymentStatus = "Full Paid";
                }elseif($order->invoice->payment_status == 1){
                    $paymentStatus = "Half Paid";
                }else{
                    $paymentStatus = "Pending";
                }
            }
            return [
                'order_id'=> config('app.order_prefix').$order->order_number,
                'amount'=>$order->total_amount ?? 0,
                'payment_status' => $paymentStatus,    
                'date'=>date(
                    'd M Y',
                    strtotime($order->created_at)
                )
            ];
        });



    /*
    |--------------------------------------------------------------------------
    | Points Earned
    |--------------------------------------------------------------------------
    */

    $pointsEarned = WalletTransaction::where('user_id',$user->id)
        ->where('type','credit')
        ->where('points','>',0)
        ->orderBy('id','desc')
        ->get()
        ->map(function($item){

            return [
                'points'=>$item->points,
                'source'=>$item->source,
                'date'=>date(
                    'd M Y',
                    strtotime($item->created_at)
                )
            ];

        });



    /*
    |--------------------------------------------------------------------------
    | Points Redeemed + Channel
    |--------------------------------------------------------------------------
    */

    $pointsRedeemed = WalletTransaction::where('user_id',$user->id)
        ->where('type','debit')
        ->where('points','>',0)
        ->orderBy('id','desc')
        ->get()
        ->map(function($item){

            return [

                'points'=>$item->points,

                'channel'=>$item->channel,

                'source'=>$item->source,

                'redeemed_by'=>$item->redeemedBy->name ?? null,

                'date'=>date(
                    'd M Y',
                    strtotime($item->created_at)
                )

            ];

        });



    /*
    |--------------------------------------------------------------------------
    | Lounge Earned
    |--------------------------------------------------------------------------
    */

    $loungeEarned = WalletTransaction::where('user_id',$user->id)
        ->where('type','credit')
        ->where('lounge_visits','>',0)
        ->orderBy('id','desc')
        ->get()
        ->map(function($item){

            return [
                'lounge_visits'=>$item->lounge_visits,
                'source'=>$item->source,
                'date'=>date(
                    'd M Y',
                    strtotime($item->created_at)
                )
            ];

        });



    /*
    |--------------------------------------------------------------------------
    | Lounge Redeemed
    |--------------------------------------------------------------------------
    */

    $loungeRedeemed = WalletTransaction::where('user_id',$user->id)
        ->where('type','debit')
        ->where('lounge_visits','>',0)
        ->orderBy('id','desc')
        ->get()
        ->map(function($item){

            return [
                'lounge_visits'=>$item->lounge_visits,
                'channel'=>$item->channel,
                'source'=>$item->source,
                'redeemed_by'=>$item->redeemedBy->name ?? null,
                'date'=>date(
                    'd M Y',
                    strtotime($item->created_at)
                )
            ];

        });



    return response()->json([

        'status'=>true,

        'customer'=>[
            'id'=>$customer->id,
            'name'=>$customer->name,
            'mobile'=>$customer->phone
        ],


        'past_purchases'=>$purchases,


        'points'=>[
            'earned'=>$pointsEarned,
            'redeemed'=>$pointsRedeemed
        ],


        'lounge'=>[
            'earned'=>$loungeEarned,
            'redeemed'=>$loungeRedeemed
        ]

    ]);
}





  

}