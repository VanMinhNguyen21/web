<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\StoreShapeRequest;
use App\Http\Resources\ShapeResource;
use App\Models\Shape;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShapeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $name = $request->name;
        $shape = Shape::query() ;

        $result = $shape->when(!empty($name), function ($q) use ($name) {
            return $q->where("name", "like", "%". ${"name"}. "%");
        })->with('product')->orderBy("id","DESC")->get(); 

        return response()->json([
            "status" => 200,
            "data" => $result,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreShapeRequest $request)
    {
        //
        try {
            $data = $request->all();
            $result = Shape::create($data);

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Create shape successfully!'
                ]
            );

        } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in create shape',
                'error' => $error,
            ]);
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
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    public function update(StoreShapeRequest $request, $id)
    {
        //
        $data = $request->all();
            $shape = Shape::with('product')->findOrFail($id);

            $shape->update($data);
            $shapeResource = new ShapeResource($shape);
            if($shapeResource){
                return response()->json([
                    'status' => 200,
                    'message' => 'Update shape successfully!',
                    'data' => $shapeResource,
                ],Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => 400,
                    'message' => 'Update shape fail!',
                ],Response::HTTP_BAD_REQUEST);
            }    
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
        // $shape = Shape::findOrFail($id);

        // $shape->update(['deleted_at'=>Carbon::now()]);

        // return response()->json([
        //     'message' => 'delete shape successful',
        // ],Response::HTTP_OK);
        Shape::findOrFail($id)->delete();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Delete shape successfully!',
        ]);
    }
}
