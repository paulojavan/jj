<?php

namespace Database\Factories;

use App\Models\Parcela;
use App\Models\Ticket;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parcela>
 */
class ParcelaFactory extends Factory
{
    protected $model = Parcela::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isPago = $this->faker->boolean(70); // 70% chance de estar pago
        
        return [
            'ticket' => function () {
                return Ticket::factory()->create()->ticket;
            },
            'id_cliente' => Cliente::factory(),
            'id_autorizado' => $this->faker->optional()->numberBetween(1, 100),
            'numero' => $this->faker->numberBetween(1, 12),
            'data_vencimento' => $this->faker->dateTimeBetween('-1 year', '+6 months'),
            'data_pagamento' => $isPago ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'hora' => $this->faker->optional()->time(),
            'valor_parcela' => $this->faker->randomFloat(2, 50, 1000),
            'valor_pago' => $isPago ? $this->faker->randomFloat(2, 50, 1000) : null,
            'dinheiro' => $this->faker->optional()->randomFloat(2, 0, 500),
            'pix' => $this->faker->optional()->randomFloat(2, 0, 500),
            'cartao' => $this->faker->optional()->randomFloat(2, 0, 500),
            'metodo' => $this->faker->optional()->randomElement(['dinheiro', 'pix', 'cartao']),
            'id_vendedor' => $this->faker->optional()->numberBetween(1, 10),
            'status' => $isPago ? 
                $this->faker->randomElement(['pago', 'paga', 'quitado', 'quitada']) : 
                $this->faker->randomElement(['em_aberto', 'pendente', 'vencido']),
            'bd' => $this->faker->word(),
            'ticket_pagamento' => $this->faker->optional()->regexify('[A-Z0-9]{20}'),
            'lembrete' => $this->faker->optional()->word(),
        ];
    }

    /**
     * Indicate that the parcela is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_pagamento' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(['pago', 'paga', 'quitado', 'quitada']),
            'valor_pago' => $attributes['valor_parcela'] ?? $this->faker->randomFloat(2, 50, 1000),
        ]);
    }

    /**
     * Indicate that the parcela is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_vencimento' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'data_pagamento' => null,
            'status' => 'vencido',
            'valor_pago' => null,
        ]);
    }

    /**
     * Indicate that the parcela is a return.
     */
    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'devolucao',
            'data_pagamento' => null,
            'valor_pago' => null,
        ]);
    }
}