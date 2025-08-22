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
    Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('logo_url')->nullable();
    $table->string('address')->nullable();
    $table->string('working_hours')->nullable();
    $table->string('about_image_url')->nullable();
    $table->text('about_description')->nullable();
    $table->longText('terms_and_conditions')->nullable();
    $table->string('facebook_url')->nullable();
    $table->string('whatsapp_number')->nullable();
    $table->string('phone_number')->nullable();
    $table->string('second_phone_number')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting');
    }
};
