<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // علاقة مع اليوزر (العميل)
            $table->foreignId('customer_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // علاقة مع الطاولة (NULL لو اتحذفت)
            $table->unsignedBigInteger('table_id')->nullable();
            $table->foreign('table_id')
                  ->references('id')->on('tables')
                  ->onDelete('set null');

            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['Pending', 'In Preparation', 'Ready', 'Delivered'])
                  ->default('Pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};