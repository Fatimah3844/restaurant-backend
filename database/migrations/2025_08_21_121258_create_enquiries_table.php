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
    Schema::create('enquiries', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
    $table->boolean('received')->default(false); // جديد
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
