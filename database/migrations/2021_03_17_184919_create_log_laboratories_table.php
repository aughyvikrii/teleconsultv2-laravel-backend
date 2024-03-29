<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogLaboratoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_laboratories', function (Blueprint $table) {
            $table->id('llid');

            $table->foreignId('labid')
                ->references('labid')
                ->on('laboratories');

            $table->foreignId('aid')
                ->references('aid')
                ->on('appointments');

            $table->text('recommendation')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('allergy')->nullable();
            
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
        Schema::dropIfExists('log_laboratories');
    }
}
