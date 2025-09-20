<?php

namespace Tests\Unit\Api\V1\App\Models;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;
    #[Test]
    public function it_has_a_belongs_to_many_companies_relationship(): void
    {
        $user = User::factory()->create();

        $organization = Organization::factory()->create([
            'owner_id' => $user->id,  // ✅ Avoid FK constraint violation
            'parent_id' => null       // ✅ Ensure no invalid FK for parent
        ]);

        $user->organizations()->attach($organization->id);

        $this->assertTrue(
            $user->organizations->contains($organization)
        );
    }
}
