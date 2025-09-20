<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class OrganizationUserInviteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory()->create()->id,
            'email' => $this->faker->unique()->safeEmail,
            'role' => 'member',
            'token' => $this->faker->uuid,
            'accepted_at' => null,
            'rejected_at' => null,
        ];
    }
}
