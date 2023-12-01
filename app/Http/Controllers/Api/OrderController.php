<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if(!is_null($request->query('status'))){
            $order = Order::with('order_detail')->where('status',$request->status)->get();
        }else{
            $order = Order::with('order_detail')->get();
        }

        if($order){
            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $order
            ],Response::HTTP_OK);
        }
       
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
        // Đây là  function checkout luôn
        try{
        $randomOrderCode = strtoupper(Str::random(8));
        $address = $request->address;
        $status = 1;
        
        $carts = Cart::where('user_id',auth()->user()->id)->get();
        $totalPrice = 0;
        foreach ($carts as $cart) {
            $product= Product::findOrFail($cart->product_id);
            $totalPrice += $cart->quantity * $product->price_old;
        }

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'total_price' => $totalPrice,
            'order_code' => $randomOrderCode,
            'address' => $address,
            'status' => 1,
            'created_at' => Carbon::now(),
        ]);

        foreach ($carts as $cart) {
            $product= Product::findOrFail($cart->product_id);

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'price' => $product->price_new ? $product->price_new : $product->price_old,
                'quantity' => $cart->quantity,
            ]);

            $cart->delete();
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Đặt hàng thành công. Mã đơn hàng ' . $randomOrderCode,
        ],Response::HTTP_OK);

    }catch (\Exception $error) {
        return response()->json([
            'status_code' => 500,
            'message' => 'Error in checkout',
            'error' => $error,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

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
        $order = Order::with('order_detail')->findOrFail($id);

        return response()->json([
            'data' => $order,
        ],Response::HTTP_OK);
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

    public function changeStatusOrder(Request $request) {
        $order = Order::findOrFail($request->order_id);

        $order->update([
            'status' => $request->status,
            'staff_id' => auth()->user()->id,
        ]);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Thay đổi trạng thái đơn hàng thành công",
        ],Response::HTTP_OK);
    }

    public function orderHistory(){
        $orderHistory = Order::with('order_detail')->where('user_id', auth()->user()->id)->get();

        return response()->json([
            'data' => $orderHistory,
        ],Response::HTTP_OK);
    }
}
