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
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('transaction_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')->references('member_id')->on('members');
            $table->unsignedBigInteger('copy_id');
            $table->foreign('copy_id')->references('copy_id')->on('book_copies');
            $table->timestamp('checkout_date');
            $table->timestamp('due_date');
            $table->timestamp('return_date');
            $table->decimal('late_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
