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
        Schema::table('written_guests', function (Blueprint $table) {
            $table->string('menu_1')->nullable()->after('children');
            $table->string('menu_2')->nullable()->after('menu_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('written_guests', function (Blueprint $table) {
            $table->dropColumn('menu_1');
            $table->dropColumn('menu_2');
        });
    }
};
