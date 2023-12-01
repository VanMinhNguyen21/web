<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Masterial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MaterialController extends Controller
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
        $material = Masterial::query() ;

        $result = $material->when(!empty($name), function ($q) use ($name) {
            return $q->where("name", "like", "%". ${"name"}. "%");
        })->with('product')->where('deleted_at', null)->orderBy("id","DESC")->get(); 

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
    public function store(StoreMaterialRequest $request)
    {
        //
        try {
            $data = $request->all();
            $result = Masterial::create($data);

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Create material successfully!'
                ]
            );

        } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in create material',
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
    public function update(StoreMaterialRequest $request, $id)
    {
        //
        $data = $request->all();
            $material = Masterial::with('product')->findOrFail($id);

            $material->update($data);
            $materialResource = new MaterialResource($material);
            if($materialResource){
                return response()->json([
                    'status' => 200,
                    'message' => 'Update material successfully!',
                    'data' => $materialResource,
                ],Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => 400,
                    'message' => 'Update material fail!',
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
        $material = Masterial::findOrFail($id);

        $material->update(['deleted_at'=>Carbon::now()]);

        return response()->json([
            'message' => 'delete material successful',
        ],Response::HTTP_OK);

        // Masterial::findOrFail($id)->delete();

        // return response()->json([
        //     'status' => Response::HTTP_OK,
        //     'message' => 'Delete material successfully!',
        // ]);
    }
}
