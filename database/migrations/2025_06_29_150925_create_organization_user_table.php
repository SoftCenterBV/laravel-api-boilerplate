<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->onDelete('cascade');
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('role')->default('member'); // Default role is 'member'
            $table->timestamps();
        });

        Schema::create('organization_user_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->onDelete('cascade');
            $table->string('email');
            $table->string('role')->default('member'); // Default role is 'member'
            $table->string('token')->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_user');
    }
};
