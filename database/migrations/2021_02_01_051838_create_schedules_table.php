<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id('scid');

            $table->foreignId('pid')
                ->references('pid')
                ->on('persons');

            $table->foreignId('bid')
                ->references('bid')
                ->on('branches');

            $table->foreignId('deid')
                ->references('deid')
                ->on('departments');
            
            $table->integer('weekday');
            $table->integer('fee');
            $table->string('start_hour', 15);
            $table->string('end_hour', 15);
            $table->integer('duration');

            $table->timestamp('created_at')->useCurrent();
            
            $table->foreignId('create_id')
                ->default('0')
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
        Schema::dropIfExists('schedules');
    }
}
