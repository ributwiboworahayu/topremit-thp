<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('recipient_id')->constrained('users');
            $table->string('transaction_code')->unique();
            $table->string('from_currency');
            $table->string('to_currency');
            $table->double('amount', 20, 8);
            $table->double('exchange_amount', 20, 8);
            $table->double('exchange_rate', 20, 8);
            $table->double('fee', 20, 8);
            $table->string('amount_type')->comment('send, receive');
            $table->foreignId('user_voucher_id')->nullable()->constrained('user_vouchers');
            $table->string('status')->default('pending');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
