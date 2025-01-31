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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('subject')->nullable();            
            $table->string('status')->nullable();
            $table->string('docType')->nullable();
            $table->string('initialDraft')->nullable();
            $table->string('finalDraft')->nullable();
            $table->string('signedCopy')->nullable();
            $table->string('holder')->nullable();
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
