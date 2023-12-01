<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuanHuyen;
use App\Models\Tinhthanhpho;
use App\Models\XaPhuong;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class XaphuongController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        // Lay thong tin tinh thanh pho
        $tinh = Tinhthanhpho::get();

        return response()->json([
            'status' =>Response::HTTP_OK,
            'data' => $tinh,
        ],Response::HTTP_OK);
    }

    public function getQuanHuyen(Request $request) {
        $tinh_id = $request->tinh_id;
        $quan = QuanHuyen::where('matp',$tinh_id)->get();

        return response()->json([
            'status' =>Response::HTTP_OK,
            'data' => $quan,
        ],Response::HTTP_OK);
    }

    public function getXaPhuong(Request $request ) {
        $quan_id = $request->quan_id;
        $xa = XaPhuong::where('maqh',$quan_id)->get();

        return response()->json([
            'status' =>Response::HTTP_OK,
            'data' => $xa,
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
