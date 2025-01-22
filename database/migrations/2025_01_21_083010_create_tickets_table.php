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
    Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->enum('priority', ['low', 'medium', 'high'])->default('low');
    $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Created by
    $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Assigned IT staff
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
