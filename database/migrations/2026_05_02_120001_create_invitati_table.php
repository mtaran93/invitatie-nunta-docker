<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitati', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('person_number');
            $table->integer('kid_number');
            $table->boolean('accommodation');
            $table->boolean('confirmed');
            $table->foreignId('wedding_table_id')
                ->nullable()
                ->constrained('wedding_tables')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitati');
    }
};
