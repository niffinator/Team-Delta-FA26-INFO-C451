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
            // Use InnoDB engine for foreign key support
            $table->engine('InnoDB');

            $table->bigIncrements('hold_id');

            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')
                ->references('member_id')
                ->on('members');
                
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')
                ->references('book_id')
                ->on('books');

            $table->dateTime('hold_date');

            $table->enum('status', [
                'active',
                'fulfilled',
                'cancelled'
            ])->default('active');

            // Enforce one active book per member/book
            $table->unsignedBigInteger('active_member_id')
                ->nullable()
                ->virtualAs(
                    "CASE WHEN status = 'active' THEN member_id ELSE NULL END"
                );

            // Enforcement 
            $table->unique(
                ['book_id', 'active_member_id'],
                'holds_one_active_book_per_member_book'
            );

            // Queue index for efficient hold retrieval
            $table->index(
                ['book_id', 'status', 'hold_date', 'hold_id'],
                'holds_queue_index'
            );
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
