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
        Schema::create('post_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained(config('filamentblog.tables.prefix').'posts')->onDelete('cascade');
            $table->foreignId('integration_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending')->comment('pending, published, failed');
            $table->string('external_id')->nullable()->comment('ID поста в социальной сети');
            $table->string('external_url')->nullable()->comment('Ссылка на пост в социальной сети');
            $table->text('error_message')->nullable()->comment('Сообщение об ошибке при публикации');
            $table->timestamp('published_at')->nullable()->comment('Время публикации');
            $table->integer('retry_count')->default(0)->comment('Количество попыток публикации');
            $table->timestamps();

            $table->index(['post_id', 'integration_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_publications');
    }
};
