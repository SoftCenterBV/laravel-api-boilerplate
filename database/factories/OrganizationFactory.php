<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'name' => $this->faker->company,
            'vat_number' => $this->faker->numerify('###########'),
            'chamber_of_commerce' => $this->faker->numerify('########'),
            'street' => $this->faker->streetName,
            'street_number' => $this->faker->buildingNumber,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->countryCode,
            'billing_email' => $this->faker->email,
            'billing_details' => $this->faker->text(100),
            'parent_id' => null,
            'owner_id' => null,
            'metadata' => json_encode([]),
        ];
    }
}
