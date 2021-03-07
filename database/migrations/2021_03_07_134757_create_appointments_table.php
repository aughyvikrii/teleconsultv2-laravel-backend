<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('aid');
            $table->foreignId('patient_id')
                    ->references('pid')
                    ->on('persons');

            $table->foreignId('scid')
                    ->references('scid')
                    ->on('schedules');

            $table->date('consul_date');
            $table->time('consul_time');
            
            $table->text('main_complaint');
            $table->text('disease_history')->nullable()->default(null);
            $table->text('allergy')->nullable()->default(null);
            $table->string('body_temperature', 25)->nullable()->default(null);
            $table->string('blood_pressure', 25)->nullable()->default(null);
            $table->string('weight', 25)->nullable()->default(null);
            $table->string('height', 25)->nullable()->default(null);

            $table->string('status')
                ->default('waiting_payment')
                ->comment('waiting_payment, waiting_consul, done, cancel, payment_expire');

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
        Schema::dropIfExists('appointments');
    }
}
