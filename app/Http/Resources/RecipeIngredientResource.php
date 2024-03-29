<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecipeIngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->load('recipes');
        $amount = null;
        foreach ($this->recipes as $recipe) {
            if ($recipe->pivot->ingredient_id === $this->id) {
                $amount = $recipe->pivot->amount;
            }
        }

        return [
            'name' => $this->name,
            'amount' => $amount,
        ];
    }
}
