<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name');
            $table->boolean('answer')->default(false);
            $table->integer('persons')->default(1);
            $table->json('persons_names')->nullable();
            $table->boolean('needs_accommodation')->default(false);
            $table->boolean('needs_transportation')->default(false);
            $table->boolean('accommodation')->default(false);
            $table->boolean('transportation')->default(false);
            $table->string('link')->unique()->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
