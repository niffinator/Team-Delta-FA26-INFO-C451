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
        Schema::create('holds', function (Blueprint $table) {

            $table->bigIncrements('hold_id');

            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')
                ->references('member_id')
                ->on('members');
                
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')
                ->references('book_id')
                ->on('books');

            $table->date('hold_date');

            $table->enum('status', [
                'active',
                'fulfilled',
                'cancelled'
            ])->defualt('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hold');
    }
};
