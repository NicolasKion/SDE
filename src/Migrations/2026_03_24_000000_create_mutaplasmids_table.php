<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutaplasmids', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('output_type_id')->index();
            $table->timestamps();
        });

        Schema::create('mutaplasmid_applicable_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mutaplasmid_id')->index();
            $table->unsignedBigInteger('input_type_id')->index();

            $table->unique(['mutaplasmid_id', 'input_type_id']);
        });

        Schema::create('mutaplasmid_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mutaplasmid_id')->index();
            $table->unsignedBigInteger('attribute_id');
            $table->float('min');
            $table->float('max');
            $table->boolean('high_is_good')->default(true);

            $table->unique(['mutaplasmid_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutaplasmid_attributes');
        Schema::dropIfExists('mutaplasmid_applicable_types');
        Schema::dropIfExists('mutaplasmids');
    }
};
