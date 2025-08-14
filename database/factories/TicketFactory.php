<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_cliente' => Cliente::factory(),
            'ticket' => 'TK' . $this->faker->unique()->numerify('##################'),
            'data' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'valor' => $this->faker->randomFloat(2, 100, 5000),
            'entrada' => $this->faker->randomFloat(2, 0, 500),
            'parcelas' => $this->faker->numberBetween(1, 12),
            'spc' => $this->faker->optional()->randomElement(['sim', 'nao']),
        ];
    }
}