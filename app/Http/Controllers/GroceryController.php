<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroceryRequest;
use App\Http\Requests\UpdateGroceryRequest;
use App\Http\Resources\GroceryResource;
use App\Models\Grocery;

class GroceryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $groceries = Grocery::where('user_id', $user->id)->get();

        return GroceryResource::collection($groceries);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGroceryRequest $request)
    {
        $grocery = new Grocery();
        $grocery->name = $request->name;
        $grocery->amount = $request->amount;
        $grocery->user_id = auth()->user()->id;
        $grocery->save();

        return new GroceryResource($grocery);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Grocery  $grocery
     * @return \Illuminate\Http\Response
     */
    public function show(Grocery $grocery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Grocery  $grocery
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroceryRequest $request, Grocery $grocery)
    {
        $user = auth()->user();
        if ($grocery->user_id !== $user->id) {
            abort(404);
        }
        $grocery->name = $request->name;
        $grocery->amount = $request->amount;
        $grocery->save();

        return new GroceryResource($grocery);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Grocery  $grocery
     * @return \Illuminate\Http\Response
     */
    public function destroy(Grocery $grocery)
    {
        //
    }
}
