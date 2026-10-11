<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->engine('InnoDB');

            $table->bigIncrements('transaction_id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users');

            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')
                ->references('member_id')
                ->on('members');

            $table->unsignedBigInteger('copy_id');
            $table->foreign('copy_id')
                ->references('copy_id')
                ->on('book_copies');

            $table->timestamp('checkout_date');
            $table->timestamp('due_date');
            $table->timestamp('return_date')->nullable();
            $table->decimal('late_fee', 8, 2)->default(0);

            // Virtual column for tracking open transactions per copy
            $table->unsignedBigInteger('open_copy_id')
                ->nullable()
                ->virtualAs(
                    "CASE WHEN return_date IS NULL THEN copy_id ELSE NULL END"
                );

            // Unique constraint for open transactions per copy
            $table->unique(
                'open_copy_id',
                'transactions_one_open_per_copy'
            );
        });

        // Database constraints to conform to business rules
        DB::statement("
            ALTER TABLE transactions
            ADD CONSTRAINT transactions_due_date_valid
                CHECK (due_date >= checkout_date),
            ADD CONSTRAINT transactions_return_date_valid
                CHECK (
                    return_date IS NULL
                    OR return_date >= checkout_date
                ),
            ADD CONSTRAINT transactions_late_fee_nonnegative
                CHECK (late_fee >= 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
