<?php

namespace App\Http\Controllers\Api\Customer_Loyality_Api;

use App\Http\Controllers\Controller;
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

            return response()->json([
                'status' => true,
                'type'   => 'active',
                'data'   => [

                    // remaining usable points
                    'active_points' => $user->total_points ?? 0,

                    // remaining lounge
                    'active_lounge' =>
                        ($user->lounge_visits_total ?? 0)
                        -
                        ($user->lounge_visits_used ?? 0),
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





  

}