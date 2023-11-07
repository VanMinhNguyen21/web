<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $name = $request->name;
        $slider = Slider::query() ;

        $result = $slider->when(!empty($name), function ($q) use ($name) {
            return $q->where("name", "like", "%". ${"name"}. "%");
        })->orderBy("id","DESC")->get(); 

        return response()->json([
            "status" => 200,
            "data" => $result,
        ]);
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

        $pathThumbnail = $request->file('image')->store('public/product');
        $array = explode('/', $pathThumbnail);
        array_shift($array);
        $image = implode('/', $array);
        $imagePath = asset('storage') . '/' . $image;

        $slider = new Slider();

        $data = [
            "name" => $request->name,
            "image" => $imagePath,
        ];

        $slider->create($data);

        return response()->json([
            "status" => 200,
            "message" => "create slider successfully",
        ]);
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
        $slider = Slider::find($id);

        if($slider) {
            $slider->delete();

            return response()->json([
                "status" => 200,
                "message" => "delete slider successfully"
            ]);
        }
        return response()->json([
            "status" => 500,
            "message" => "Error when delete slider"
        ]);

    }
}
