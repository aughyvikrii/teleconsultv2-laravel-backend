<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogSoapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_soaps', function (Blueprint $table) {
            $table->id('lsid');

            $table->foreignId('soid')
                ->references('soid')
                ->on('soaps');

            $table->foreignId('aid')
                ->references('aid')
                ->on('appointments');

            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assesment')->nullable();
            $table->text('plan')->nullable();

            $table->timestamp('created_at')
                ->useCurrent();
    
            $table->foreignId('create_id')
                ->references('uid')
                ->on('users');

            $table->timestamp('last_update')
                ->nullable(true)
                ->default(null);

            $table->foreignId('delete_id')
                ->nullable(true)
                ->default(null)
                ->references('uid')
                ->on('users');
                
            $table->timestamp('deleted_at')
                ->nullable(true)
                ->default(null);

            $table->boolean('is_active')
                ->default(true);
                
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_soaps');
    }
}
