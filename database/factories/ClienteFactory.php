<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'apelido' => $this->faker->optional()->firstName(),
            'rg' => $this->faker->numerify('##.###.###-#'),
            'cpf' => $this->faker->numerify('###.###.###-##'),
            'mae' => $this->faker->optional()->name('female'),
            'pai' => $this->faker->optional()->name('male'),
            'nascimento' => $this->faker->optional()->date(),
            'telefone' => $this->faker->numerify('###########'),
            'nome_referencia' => $this->faker->name(),
            'numero_referencia' => $this->faker->numerify('###########'),
            'parentesco_referencia' => $this->faker->randomElement(['pai', 'mae', 'irmao', 'irma', 'amigo']),
            'referencia_comercial1' => $this->faker->company(),
            'telefone_referencia_comercial1' => $this->faker->numerify('###########'),
            'referencia_comercial2' => $this->faker->company(),
            'telefone_referencia_comercial2' => $this->faker->numerify('###########'),
            'referencia_comercial3' => $this->faker->company(),
            'telefone_referencia_comercial3' => $this->faker->numerify('###########'),
            'foto' => 'default.jpg',
            'rg_frente' => $this->faker->optional()->word() . '.jpg',
            'rg_verso' => $this->faker->optional()->word() . '.jpg',
            'cpf_foto' => $this->faker->optional()->word() . '.jpg',
            'rua' => $this->faker->streetName(),
            'numero' => $this->faker->buildingNumber(),
            'bairro' => $this->faker->citySuffix(),
            'referencia' => $this->faker->optional()->sentence(3),
            'cidade' => $this->faker->city(),
            'limite' => $this->faker->randomFloat(2, 0, 5000),
            'renda' => $this->faker->randomElement(['1000-2000', '2000-3000', '3000-5000', '5000+']),
            'status' => $this->faker->randomElement(['ativo', 'inativo']),
            'atualizacao' => $this->faker->optional()->date(),
            'token' => $this->faker->numerify('####'),
            'obs' => $this->faker->optional()->sentence(),
            'ociosidade' => $this->faker->optional()->date(),
            'pasta' => $this->faker->optional()->uuid(),
        ];
    }
}
