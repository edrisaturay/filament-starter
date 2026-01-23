<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starter_panel_plugin_overrides', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('panel_id')->index();
            $blueprint->string('plugin_key')->index();
            $blueprint->boolean('enabled')->nullable();
            $blueprint->json('options')->nullable();
            $blueprint->integer('options_version')->default(1);
            $blueprint->string('tenant_id')->nullable()->index();
            $blueprint->unsignedBigInteger('updated_by_user_id')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('starter_panel_snapshots', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('panel_id')->unique();
            $blueprint->json('meta');
            $blueprint->timestamp('last_seen_at')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('starter_audit_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('actor_user_id')->nullable();
            $blueprint->string('action');
            $blueprint->string('panel_id')->nullable();
            $blueprint->string('plugin_key')->nullable();
            $blueprint->json('before')->nullable();
            $blueprint->json('after')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starter_panel_plugin_overrides');
        Schema::dropIfExists('starter_panel_snapshots');
        Schema::dropIfExists('starter_audit_logs');
    }
};
