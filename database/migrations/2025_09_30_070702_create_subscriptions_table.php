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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('customer_key'); // Уникальный ключ клиента для T-Bank
            $table->decimal('amount', 10, 2);
            $table->string('currency');
            $table->string('status'); // active, cancelled, expired
            $table->string('external_subscription_id')->nullable();
            $table->string('rebill_id')->nullable(); // RebillId от T-Bank для автоплатежей
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
