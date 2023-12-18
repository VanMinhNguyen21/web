<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\QuanHuyen;
use App\Models\Tinhthanhpho;
use App\Models\XaPhuong;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class  OrderController extends Controller
{
    // const ORDER_CANCEL = 4;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if(!is_null($request->query('status'))){
            $order = Order::with(['order_detail',"order_detail.product", 'user'])->where('status',$request->status)->orderBy('id',"desc")->get();
        }else{
            $order = Order::with(['order_detail',"order_detail.product", 'user'])->get();
        }

        if($order){
            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $order
            ],Response::HTTP_OK);
        }
       
    }

    public function getOrderByCode(Request $request)
    {
        //
        if(!is_null($request->query('status'))){
            $order = Order::with('order_detail',"order_detail.product", "user")
            ->where('status',$request->status)
            ->where("order_code", "like", "%". $request->order_code. "%")
            ->orderBy('id',"desc")->get();
        }else{
            $order = Order::with('order_detail',"order_detail.product", 'user')->get();
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
    //     try{

    //     $randomOrderCode = 'ANNA-' . strtoupper(Str::random(8));
    //     $tinh  = Tinhthanhpho::find($request->tinh);
    //     $quan = QuanHuyen::find($request->quan);
    //     $xa = XaPhuong::find($request->xa);
    //     $district = $request->duong;
        
    //     $address = $district. " - " .  $xa->name . " - " . $quan->name . " - " . $tinh->name;
    //     $status = 1;
        
    //     $carts = Cart::where('user_id',auth()->user()->id)->get();
    //     $totalPrice = 0;
    //     foreach ($carts as $cart) {
    //         $product= Product::findOrFail($cart->product_id);
    //         if($product->price_new == null) {
    //             $totalPrice += $cart->quantity * $product->price_old;
    //         }else{
    //             $totalPrice += $cart->quantity * $product->price_new;
    //         }
    //     }

    //     $order = Order::create([
    //         'user_id' => auth()->user()->id,
    //         'total_price' => $totalPrice,
    //         'order_code' => $randomOrderCode,
    //         'address' => $address,
    //         'status' => 1,
    //         'created_at' => Carbon::now(),
    //         "name" => $request->name,
    //         "phone" => $request->phone,
    //         "note" => $request->note
    //     ]);

    //     foreach ($carts as $cart) {
    //         $product= Product::findOrFail($cart->product_id);

    //         OrderDetail::create([
    //             'order_id' => $order->id,
    //             'product_id' => $cart->product_id,
    //             'price' => $product->price_new ? $product->price_new : $product->price_old,
    //             'quantity' => $cart->quantity,
    //         ]);

    //         $product->update(['quantity' => $product->quantity - $cart->quantity]);

    //         $cart->delete();
    //     }

    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => 'Đặt hàng thành công. Mã đơn hàng ' . $randomOrderCode,
    //     ],Response::HTTP_OK);

    // }catch (\Exception $error) {
    //     return response()->json([
    //         'status_code' => 500,
    //         'message' => 'Error in checkout',
    //         'error' => $error,
    //     ], Response::HTTP_INTERNAL_SERVER_ERROR);
    // }

    try {
        $randomOrderCode = 'ANNA-' . strtoupper(Str::random(8));
        $tinh  = Tinhthanhpho::find($request->tinh);
        $quan = QuanHuyen::find($request->quan);
        $xa = XaPhuong::find($request->xa);
        $district = $request->duong;
        
        $address = $district. " - " .  $xa->name . " - " . $quan->name . " - " . $tinh->name;
        $status = 1;
        // Tính tổng giá trị đơn hàng
        $carts = Cart::where('user_id', auth()->user()->id)->get();
        $totalPrice = 0;

        foreach ($carts as $cart) {
            $product = Product::findOrFail($cart->product_id);
            if ($product->price_new == null) {
                $totalPrice += $cart->quantity * $product->price_old;
            } else {
                $totalPrice += $cart->quantity * $product->price_new;
            }
        }

        $shippingFee = $totalPrice >= 2000000 ? 0 : 40000;

        // Tạo đơn hàng
        $order = Order::create([
            'user_id' => auth()->user()->id,
            'total_price' => $totalPrice + $shippingFee, // Tổng giá trị đơn hàng cộng phí vận chuyển
            'order_code' => $randomOrderCode,
            'address' => $address,
            'status' => 1,
            'created_at' => Carbon::now(),
            "name" => $request->name,
            "phone" => $request->phone,
            "note" => $request->note,
            "payment_method" => 1
        ]);

        // Tạo chi tiết đơn hàng và cập nhật số lượng sản phẩm
        foreach ($carts as $cart) {
            $product = Product::findOrFail($cart->product_id);

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'price' => $product->price_new ? $product->price_new : $product->price_old,
                'quantity' => $cart->quantity,
            ]);

            $product->update(['quantity' => $product->quantity - $cart->quantity]);
            $cart->delete();
        }

        // Trả về kết quả
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Đặt hàng thành công. Mã đơn hàng ' . $randomOrderCode,
        ], Response::HTTP_OK);

    } catch (\Exception $error) {
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
        $order = Order::with('order_detail',"order_detail.product")->findOrFail($id);

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
        $order = Order::findOrFail($id);
        $order_details = OrderDetail::where("order_id",$order->id)->get();
        foreach($order_details as $order_detail ) {
            $product = Product::where("id",$order_detail->product_id)->first();

            $product->update(['quantity' => $product->quantity + $order_detail->quantity]);
        }

        $order->update([
            'status' => Order::STATUS_CANCEL,
        ]);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "hủy đơn hàng thành công",
        ],Response::HTTP_OK);
    }

    public function changeStatusOrder(Request $request) {
        $order = Order::findOrFail($request->order_id);

        $order->update([
            'status' => $request->status,
            // 'staff_id' => auth()->user()->id,
        ]);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Thay đổi trạng thái đơn hàng thành công",
        ],Response::HTTP_OK);
    }

    public function orderHistory(Request $request){
        $orderHistory = Order::with('order_detail','order_detail.product')->where('user_id', auth()->user()->id)->orderBy('id',"desc")->get();

        return response()->json([
            'data' => $orderHistory,
        ],Response::HTTP_OK);

        // $status = $request->query('status');

        // $orderHistory = Order::with('order_detail', 'order_detail.product')
        //     ->where('user_id', auth()->user()->id);

        // if (!is_null($status)) {
        //     $orderHistory->where('status', $status);
        // }

        // $orderHistory = $orderHistory->orderBy('id', 'desc')->get();

        // return response()->json([
        //     'data' => $orderHistory,
        // ], Response::HTTP_OK);

    }
}
