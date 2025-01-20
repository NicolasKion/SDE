<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->boolean('has_types')->default(false)->index();
            $table->foreignId('icon_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('parent_id')->nullable()->constrained('market_groups')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_groups');
    }
};
