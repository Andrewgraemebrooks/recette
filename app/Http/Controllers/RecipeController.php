<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $recipes = Recipe::all();
        return RecipeResource::collection($recipes);
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
     * @param  \App\Http\Requests\RecipeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecipeRequest $request)
    {
        $recipe = new Recipe();
        $recipe->name = $request->name;
        $recipe->save();
        foreach ($request->ingredients as $ingredient) {
            $name = $ingredient['name'];
            $amount = $ingredient['amount'];
            $new_ingredient = new Ingredient();
            $new_ingredient->name = $name;
            $recipe->ingredients()->save($new_ingredient, ['amount' => $amount]);
        }
        if ($request->images) {
            foreach ($request->images as $image) {
                Storage::put($image->name, $image);
            }
        }
        return new RecipeResource($recipe);
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
    public function update(Request $request, $id)
    {
        $recipe = Recipe::where('id', $id)->first();
        $recipe->name = $request->name;
        $recipe->save();
        return new RecipeResource($recipe);
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
