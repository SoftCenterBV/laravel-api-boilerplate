<?php

namespace Tests\Unit\Api\V1\App\Notifications;

use App\Models\User;
use App\Notifications\OrganizationAccessInvite;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrganizationAccessInviteTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    public function testNotificationViaMail()
    {

        Notification::fake();
        $user = User::factory()->create();
        Notification::send($user, new OrganizationAccessInvite('test-token'));
        Notification::assertSentTo(
            $user,
            OrganizationAccessInvite::class
        );

    }

}
