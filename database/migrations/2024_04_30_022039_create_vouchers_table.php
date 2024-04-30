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
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('discount_percentage');
            $table->integer('point_required');
            $table->integer('stock');
            $table->integer('total_redeemed')->default(0);
            $table->integer('redeem_limit')->default(1); // limit how many times a voucher can be redeemed (default 1 time
            $table->text('description');
            $table->dateTime('start_date');
            $table->dateTime('expired_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};
