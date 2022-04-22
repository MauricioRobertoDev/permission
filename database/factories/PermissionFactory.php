<?php

namespace MrDev\Permission\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MrDev\Permission\Models\Permission;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition()
    {
        return [
            'key' => $this->faker->slug(2),
            'name' => $this->faker->sentence(2),
            'guard_name' => 'web',
            'description' => $this->faker->sentence(),
        ];
    }
}
