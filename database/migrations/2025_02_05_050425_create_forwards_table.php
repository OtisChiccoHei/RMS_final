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
        Schema::create('forwards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('documentId')->nullable();
            $table->string('document_name')->nullable();
            $table->string('document_description')->nullable();
            $table->string('document_type')->nullable();
            $table->string('sender')->nullable();
            $table->string('sender_division')->nullable();
            $table->string('remarks')->nullable();
            $table->string('receiver')->nullable();
            $table->string('receiver_division')->nullable();
            $table->string('receiver_divisionTemp')->nullable();
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forwards');
    }
};
