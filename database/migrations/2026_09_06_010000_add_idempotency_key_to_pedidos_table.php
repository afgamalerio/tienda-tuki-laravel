<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->after('user_id');
            $table->unique(['user_id', 'idempotency_key'], 'pedidos_user_id_idempotency_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropUnique('pedidos_user_id_idempotency_key_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
