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
        Schema::create('project_payment_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->decimal('target_amount', 10, 2)->comment('Целевая сумма для проекта');
            $table->decimal('current_amount', 10, 2)->default(0)->comment('Текущая накопленная сумма');
            $table->string('currency', 3)->default('RUB')->comment('Валюта (USD, EUR, RUB и т.д.)');
            $table->text('description')->nullable()->comment('Описание цели сбора средств');
            $table->date('deadline')->nullable()->comment('Крайний срок сбора средств');
            $table->boolean('is_active')->default(true)->comment('Активна ли цель');
            $table->timestamps();

            $table->index(['project_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_payment_goals');
    }
};
