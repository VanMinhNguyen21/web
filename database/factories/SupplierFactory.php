<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'telephone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'description' =>$this->faker->text(),
            'created_at' =>now(),
            'updated_at' =>now(),
        ];
    }
}
