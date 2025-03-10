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
        Schema::create('logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('docId')->nullable();
            $table->string('doc_name')->nullable(); 
            $table->string('doc_description')->nullable();
            $table->string('doc_type')->nullable();
            $table->string('user')->nullable();
            $table->string('user_division')->nullable();
            $table->string('transaction')->nullable();
            $table->string('recipient')->nullable();
            $table->string('recipient_division')->nullable();
            $table->string('actionTaken')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
