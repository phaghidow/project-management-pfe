<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Milestone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Task>
     */
    protected $model = Task::class;

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
            'milestone_id' => Milestone::factory(),
            'start_date' => $today->copy()->subDays(5)->format('Y-m-d'),
            'end_date' => $today->copy()->addDays(10)->format('Y-m-d'),
            'due_date' => $today->copy()->addDays(15)->format('Y-m-d'),
            'status' => 'in_progress',
        ];
    }
}
