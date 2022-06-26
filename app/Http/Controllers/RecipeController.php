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
        $user = auth()->user();
        $recipes = Recipe::where('user_id', $user->id)->get();

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
        $user = auth()->user();
        $recipe = new Recipe();
        $recipe->name = $request->name;
        if ($request->rating || $request->rating === 0) {
            $recipe->rating = $request->rating;
        }
        if ($request->category_id) {
            $recipe->category_id = $request->category_id;
        }
        $recipe->user_id = $user->id;
        $recipe->save();
        foreach ($request->ingredients as $ingredient) {
            $name = $ingredient['name'];
            $amount = $ingredient['amount'];
            if (Ingredient::where(['name' => $name, 'user_id' => $user->id])->exists()) {
                $existingIngredient = Ingredient::firstWhere(['name' => $name, 'user_id' => $user->id]);
                $recipe->ingredients()->attach($existingIngredient->id, ['amount' => $amount]);
                continue;
            }
            $newIngredient = new Ingredient();
            $newIngredient->name = $name;
            $newIngredient->user_id = $user->id;
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
        $user = auth()->user();
        if ($recipe->user->id !== $user->id) {
            abort(404, 'Cannot find recipe');
        }

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
        $user = auth()->user();
        if ($recipe->user->id !== $user->id) {
            abort(404, 'Cannot find recipe');
        }
        if ($request->name) {
            $recipe->name = $request->name;
        }
        if ($request->ingredients) {
            $recipe->ingredients()->detach();
            foreach ($request->ingredients as $ingredient) {
                $name = $ingredient['name'];
                $amount = $ingredient['amount'];
                if (Ingredient::where(['name' => $name, 'user_id' => $user->id])->exists()) {
                    $existingIngredient = Ingredient::firstWhere(['name' => $name, 'user_id' => $user->id]);
                    $recipe->ingredients()->attach($existingIngredient->id, ['amount' => $amount]);
                    continue;
                }
                $newIngredient = new Ingredient();
                $newIngredient->name = $name;
                $newIngredient->user_id = $user->id;
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
        $user = auth()->user();
        if ($recipe->user->id !== $user->id) {
            abort(404, 'Cannot find recipe');
        }
        $recipe->delete();

        return response()->noContent();
    }
}
