<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $ingredients = Ingredient::where('user_id', $user->id)->get();

        return IngredientResource::collection($ingredients);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreIngredientRequest;  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIngredientRequest $request)
    {
        $user = auth()->user();
        $ingredient = new Ingredient();
        $ingredient->name = $request->name;
        $ingredient->user_id = $user->id;
        $ingredient->save();

        return new IngredientResource($ingredient);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
    public function show(Ingredient $ingredient)
    {
        $user = auth()->user();
        if ($ingredient->user->id !== $user->id) {
            abort(404, 'Cannot find ingredient');
        }

        return new IngredientResource($ingredient);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateIngredientRequest  $request
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateIngredientRequest $request, Ingredient $ingredient)
    {
        $user = auth()->user();
        if ($ingredient->user->id !== $user->id) {
            abort(404, 'Cannot find ingredient');
        }
        $ingredient->name = $request->name;
        $ingredient->save();

        return new IngredientResource($ingredient);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ingredient $ingredient)
    {
        $user = auth()->user();
        if ($ingredient->user->id !== $user->id) {
            abort(404, 'Cannot find ingredient');
        }
        $ingredient->delete();

        return response()->noContent();
    }
}
