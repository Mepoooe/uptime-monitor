<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    const TABLE_NAME = 'domains';

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('url');
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);

            // Check settings
            $table->enum('check_method', ['GET', 'HEAD'])->default('HEAD');
            $table->unsignedSmallInteger('check_interval')->default(5)
                  ->comment('Interval in minutes: 1,5,10,15,30,60');
            $table->unsignedSmallInteger('check_timeout')->default(10)
                  ->comment('Request timeout in seconds');

            // Status cache (denormalized for quick list view)
            $table->boolean('is_up')->nullable();
            $table->unsignedSmallInteger('last_status_code')->nullable();
            $table->unsignedInteger('last_response_time')->nullable()
                  ->comment('Last response time in ms');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('last_checked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
};
