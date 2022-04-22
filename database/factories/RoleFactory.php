<?php

namespace MrDev\Permission\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MrDev\MrMax\Models\Role;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'key' => $this->faker->slug(2),
            'name' => $this->faker->sentence(2),
            'description' => $this->faker->sentence(),
        ];
    }
}
