<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AnalysController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        // analys Order : total order and total price by day
        // $date_from = $request->date_from ?? now()->subDays(7);
        $date_from = $request->date_from ?? now()->startOfDay();
        $date_to =$request->date_to ?? now();

        $totalOrderByStatus = DB::table('order')
            ->select(DB::raw('DATE(order.updated_at) as order_date'), DB::raw('COUNT(order.id) as total_orders'),"order.status as status_order")
            ->whereBetween('order.updated_at', [$date_from . ' 00:00:00', $date_to . ' 23:59:59'])
            ->groupBy('order_date',"order.status")
            ->orderBy('order_date')
            ->get();

        $totalProductSell = DB::table('order_detail')
            ->selectRaw('DATE(order_detail.updated_at) as order_date, SUM(order_detail.quantity) as total_quantity_sell, order_detail.product_id as product_id, product.name')
            ->leftJoin('product', 'order_detail.product_id', '=', 'product.id')
            ->whereBetween('order_detail.updated_at', [$date_from . ' 00:00:00', $date_to . ' 23:59:59'])
            ->groupBy('order_date', 'product_id', 'product.name')
            ->orderBy('order_date')
            ->get();
        

        $totalSumMoney = DB::table('order')
        ->select(DB::raw('DATE(order.updated_at) as order_date'), DB::raw('SUM(order.total_price) as total_price'))
        ->whereBetween('order.updated_at', [$date_from . ' 00:00:00', $date_to . ' 23:59:59'])
        ->where("order.status", Order::STATUS_SUCCESS)
        ->groupBy('order_date')
        ->orderBy('order_date')
        ->get();


        $results = DB::table('order')
        ->select(DB::raw('DATE(order.created_at) as order_date'), DB::raw('COUNT(order.id) as total_orders'),DB::raw('SUM(order.total_price) as total_price'),DB::raw('SUM(order_detail.quantity) as total_quantity'),"order.status as status_order")
        ->whereBetween('order.created_at', [$date_from . ' 00:00:00', $date_to . ' 23:59:59'])
        ->leftJoin('order_detail', 'order.id', '=', 'order_detail.order_id')
        ->groupBy('order_date',"order.status")
        ->orderBy('order_date')
        ->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => [
                'total_order_by_status' => $totalOrderByStatus,
                "total_product_sell" => $totalProductSell,
                "total_sum_money" =>$totalSumMoney
            ],
        ]);
    }

    public function totalUser() {
        $totalUser = DB::table('users')->select(DB::raw("COUNT(id) as total_user"),"role")
                                ->groupBy('role')->get();
       
        return response()->json([
            'status' => Response::HTTP_OK,
            'total_user' => $totalUser,
        ],Response::HTTP_OK);
    }

    public function totalProduct() {
        $totalProduct = DB::table('product')->select(DB::raw("COUNT(product.id) as total_product"),"category.name")
                                ->leftJoin("category", "category.id", "=", "product.category_id")
                                ->groupBy("category.name")->get();
       
        return response()->json([
            'status' => Response::HTTP_OK,
            'total_product' => $totalProduct,
        ],Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
