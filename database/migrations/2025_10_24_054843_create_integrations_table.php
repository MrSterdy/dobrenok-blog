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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Название интеграции (VK, Telegram, и т.д.)');
            $table->string('type')->comment('Тип интеграции (vk, telegram, instagram, и т.д.)');
            $table->json('credentials')->comment('Учетные данные (токены, ключи API)');
            $table->json('settings')->nullable()->comment('Дополнительные настройки');
            $table->boolean('is_active')->default(true)->comment('Активна ли интеграция');
            $table->timestamps();

            $table->unique('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
