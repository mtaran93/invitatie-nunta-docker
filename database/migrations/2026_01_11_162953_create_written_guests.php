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
        Schema::create('written_guests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('answer')->default(false);
            $table->integer('persons')->default(1);
            $table->boolean('children')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_guests');
    }
};
