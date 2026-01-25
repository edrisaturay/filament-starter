<?php

namespace Archilex\AdvancedTables\Database\Factories;

use Archilex\AdvancedTables\Models\UserView;
use Archilex\AdvancedTables\Support\Config;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserViewFactory extends Factory
{
    protected $model = UserView::class;

    public function definition(): array
    {
        return [
            'user_id' => Config::getUser()::factory(),
            'name' => fake()->words(3, true),
            'resource' => '',
            'filters' => [],
            'indicators' => [],
            'is_public' => false,
            'is_global_favorite' => false,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_global_favorite' => true,
        ]);
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_global_favorite' => false,
        ]);
    }
}
