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
        Schema::create('hold', function (Blueprint $table) {
            $table->bigIncrements('hold_id');
            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')->references('member_id')->on('members');
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')->references('book_id')->on('books');
            $table->dateTime('hold_date');
            $table->char('status');
            $table->timestamps();
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
