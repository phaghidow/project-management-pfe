<?php

namespace Database\Factories;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Milestone>
 */
class MilestoneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Milestone>
     */
    protected $model = Milestone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $today = now();

        return [
            'name' => $this->faker->sentence(3),
            'project_id' => Project::factory(),
            'due_date' => $today->copy()->addMonths(1)->format('Y-m-d'),
        ];
    }
}
