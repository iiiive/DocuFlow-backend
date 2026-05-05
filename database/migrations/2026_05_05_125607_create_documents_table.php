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
        $table->id();

        // The user who owns the document
        $table->foreignId('owner_id')
            ->constrained('users')
            ->onDelete('cascade');

        // Document title
        $table->string('title')->default('Untitled Document');

        // Rich-text editor content will be saved as HTML
        $table->longText('content_html')->nullable();

        $table->timestamps();
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
