<?php

namespace Tests\Unit\Api\V1\App\Models;

use App\Models\OrganizationUserInvite;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrganizationUserInviteTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;
    //    use RefreshDatabase;
    #[Test]
    public function it_has_a_belongs_to_many_users_relationship(): void
    {
        $invite = OrganizationUserInvite::factory()->create(
            ['accepted_at' => Carbon::now()]
        );

        $this->assertTrue($invite->isAccepted());
    }

    #[Test]
    public function it_has_a_rejected_status(): void
    {
        $invite = OrganizationUserInvite::factory()->create(
            ['rejected_at' => Carbon::now()]
        );

        $this->assertTrue($invite->isRejected());
    }
}
