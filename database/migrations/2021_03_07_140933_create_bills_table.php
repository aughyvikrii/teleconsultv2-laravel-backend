<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id('blid');
            $table->string('uniq', 50);

            $table->foreignId('aid')
                    ->references('aid')
                    ->on('appointments');

            $table->text('description')->nullable();

            $table->decimal('amount', 19, 2);
            $table->string('status')
                    ->default('waiting_payment')
                    ->comment('waiting_payment, cancel, expire');

            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_on')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
    
            $table->foreignId('create_id')
                ->references('uid')
                ->on('users');

            $table->timestamp('last_update')->nullable(true)->default(null);

            $table->foreignId('delete_id')
                ->nullable(true)
                ->default(null)
                ->references('uid')
                ->on('users');
                
            $table->timestamp('deleted_at')->nullable(true)->default(null);
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills');
    }
}
