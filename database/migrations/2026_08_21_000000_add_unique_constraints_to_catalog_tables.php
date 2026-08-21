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
        Schema::table('categorias', function (Blueprint $table) {
            $table->unique('nombre', 'categorias_nombre_unique');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->unique(
                ['nombre', 'color'],
                'productos_nombre_color_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropUnique('productos_nombre_color_unique');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropUnique('categorias_nombre_unique');
        });
    }
};
