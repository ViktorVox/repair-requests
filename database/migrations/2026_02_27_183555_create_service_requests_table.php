<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('phone');
            $table->string('address');
            $table->text('problem_text');
            $table->enum('status', ['new', 'assigned', 'in_progress', 'done', 'canceled'])->default('new');
            // Внешний ключ на таблицу users. nullOnDelete() нужен, чтобы при удалении мастера заявка не удалилась, а повисла без мастера.
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};