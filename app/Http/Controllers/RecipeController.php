<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Storage;

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
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRecipeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecipeRequest $request)
    {
        $recipe = new Recipe();
        $recipe->name = $request->name;
        if ($request->rating || $request->rating === 0) {
            $recipe->rating = $request->rating;
        }
        $recipe->save();
        foreach ($request->ingredients as $ingredient) {
            $name = $ingredient['name'];
            $amount = $ingredient['amount'];
            if (Ingredient::where('name', $name)->exists()) {
                $existingIngredient = Ingredient::first('name', $name);
                $recipe->ingredients()->attach($existingIngredient->id, ['amount' => $amount]);
                continue;
            }
            $newIngredient = new Ingredient();
            $newIngredient->name = $name;
            $recipe->ingredients()->save($newIngredient, ['amount' => $amount]);
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
     * @return \App\Models\Recipe
     */
    public function show(Recipe $recipe)
    {
        return new RecipeResource($recipe);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRecipeRequest  $request
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        if ($request->name) {
            $recipe->name = $request->name;
        }
        if ($request->ingredients) {
            $recipe->ingredients()->detach();
            foreach ($request->ingredients as $ingredient) {
                $name = $ingredient['name'];
                $amount = $ingredient['amount'];
                if (Ingredient::where('name', $name)->exists()) {
                    $existingIngredient = Ingredient::first('name', $name);
                    $recipe->ingredients()->attach($existingIngredient->id, ['amount' => $amount]);
                    continue;
                }
                $newIngredient = new Ingredient();
                $newIngredient->name = $name;
                $recipe->ingredients()->save($newIngredient, ['amount' => $amount]);
            }
        }
        if ($request->images) {
            foreach ($request->images as $image) {
                Storage::put($image->name, $image);
            }
        }
        if ($request->rating) {
            $recipe->rating = $request->rating;
        }
        if ($recipe->isDirty()) {
            $recipe->save();
        }
        return new RecipeResource($recipe);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();
        return response()->noContent();
    }
}
