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
        Schema::create('book_copies', function (Blueprint $table) {

            $table->bigIncrements('copy_id');
            
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')
                ->references('book_id')
                ->on('books'); 

            $table->string('call_number');

            $table->enum('status', [
                'available',
                'checked out'
            ])->default('available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
