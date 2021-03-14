<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMidtransToBills extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('midtrans_snaptoken')->nullable();
            $table->string('midtrans_payment_type')->nullable();
            $table->text('midtrans_pending_log')->nullable();
            $table->text('midtrans_paid_log')->nullable();
            $table->text('midtrans_log')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_snaptoken',
                'midtrans_payment_type',
                'midtrans_pending_log',
                'midtrans_paid_log',
                'midtrans_log'
            ]);
        });
    }
}
