<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $categoryName = $request->categoryName;
        $query = Category::query();
        $categories = $query->when(!empty($categoryName), function ($q) use ($categoryName) {
            $q->where('name', 'LIKE', "%{$categoryName}%");
        })->with('product')->get();

        $categoriesResource =  CategoryResource::collection($categories)->response()->getData();

        return $categoriesResource;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request)
    {
        //
        try {
            $data = $request->all();
            $result = Category::create($data);

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Create category successfully!'
                ]
            );

        } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in create category',
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
            $response = Category::with('product')->findOrFail($id);
            $responseResource =  new CategoryResource($response);

            return response()->json([
                'status' => 200,
                'data' => $responseResource,
            ]);

        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCategoryRequest $request, $id)
    {
        //
            $data = $request->all();
            $category = Category::with('product')->findOrFail($id);

            $category->update($data);
            $categoryResource = new CategoryResource($category);
            if($categoryResource){
                return response()->json([
                    'status' => 200,
                    'message' => 'Update category successfully!',
                    'data' => $categoryResource,
                ],Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => 400,
                    'message' => 'Update category fail!',
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
            //code...
            Category::findOrFail($id)->delete();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Delete category successfully!',
            ]);
    }
}
