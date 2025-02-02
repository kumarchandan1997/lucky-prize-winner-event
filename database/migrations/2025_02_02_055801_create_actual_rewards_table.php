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
        Schema::create('actual_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('probability', 5, 2);
            $table->decimal('actual_percentage', 5, 2);
            $table->integer('number_of_people');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actual_rewards');
    }
};
